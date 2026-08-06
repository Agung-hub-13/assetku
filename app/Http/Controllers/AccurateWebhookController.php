<?php

namespace App\Http\Controllers;

use App\Jobs\SyncAssetFromAccurateJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AccurateWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // 🔧 TAMBAHAN: verifikasi token sebelum proses apa pun
        $expectedToken = config('services.accurate.webhook_secret');

        if (empty($expectedToken) || $request->query('token') !== $expectedToken) {
            Log::warning('ACCURATE WEBHOOK REJECTED: token tidak valid/tidak ada', [
                'ip' => $request->ip(),
            ]);
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $payload = $request->all();
        Log::info('ACCURATE WEBHOOK RECEIVED', $payload);

        $events = isset($payload[0]) && is_array($payload[0]) ? $payload : [$payload];

        $dispatchedCount = 0;

        foreach ($events as $event) {
            $type = strtoupper($event['type'] ?? '');
            $dataList = $event['data'] ?? [];

            if (empty($dataList)) {
                Log::warning('WEBHOOK SKIPPED: Array data kosong dalam event', $event);
                continue;
            }

            foreach ($dataList as $item) {
                $accurateId = $item['itemId'] ?? $item['id'] ?? null;
                if (!$accurateId) {
                    Log::warning('WEBHOOK SKIPPED: itemId/id tidak ditemukan', $item);
                    continue;
                }

                $action = $item['action'] ?? 'WRITE';
                $dedupeKey = "accurate-webhook:{$type}:{$accurateId}:{$action}";

                if (!Cache::add($dedupeKey, true, now()->addSeconds(15))) {
                    Log::info("WEBHOOK DUPLICATE SKIPPED untuk ID: {$accurateId}");
                    continue;
                }

                if (in_array($type, ['ITEM', 'FIXED_ASSET', 'FIXEDASSET'])) {
                    Log::info("DISPATCHING SyncAssetFromAccurateJob untuk ID: {$accurateId} (Type: {$type})");
                    SyncAssetFromAccurateJob::dispatch((string) $accurateId, $item);
                    $dispatchedCount++;
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Webhook Accurate diproses. Total job didispatch: {$dispatchedCount}"
        ]);
    }
}