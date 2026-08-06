<?php

namespace App\Jobs;

use App\Services\AssetSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncAssetFromAccurateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [10, 30, 90]; // detik, jeda antar retry

    // 💡 Tambahkan $extraData sebagai array opsional (default = [])
    public function __construct(
        public readonly string $accurateAssetId,
        public readonly array $extraData = []
    ) {}

    public function handle(AssetSyncService $service): void
    {
        try {
            // 💡 Teruskan $extraData ke service
            $result = $service->syncSingleAsset($this->accurateAssetId, $this->extraData);

            // Hapus error log jika sync berhasil
            \App\Models\Asset::where('accurate_fixed_asset_id', $this->accurateAssetId)
                ->orWhere('accurate_item_id', $this->accurateAssetId)
                ->whereNotNull('sync_error')
                ->update(['sync_error' => null]);

            logger()->info('ASYNC ASSET SYNC RESULT', [
                'accurate_id' => $this->accurateAssetId,
                'result'      => $result,
            ]);
        } catch (\Throwable $e) {
            // Catat error ke DB jika record aset sudah sempat dibuat sebelumnya
            \App\Models\Asset::where('accurate_fixed_asset_id', $this->accurateAssetId)
                ->orWhere('accurate_item_id', $this->accurateAssetId)
                ->update(['sync_error' => substr($e->getMessage(), 0, 255)]);

            throw $e; // Tetap throw agar Job di-retry oleh Queue
        }
    }

    public function failed(\Throwable $e): void
    {
        logger()->error('ASYNC ASSET SYNC FAILED (habis retry)', [
            'accurate_id' => $this->accurateAssetId,
            'error'       => $e->getMessage(),
        ]);
    }
}