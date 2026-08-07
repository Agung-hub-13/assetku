<?php

namespace App\Services;

use App\Models\Asset;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AssetSyncService
{
    protected AccurateService $accurate;

    public function __construct(AccurateService $accurate)
    {
        $this->accurate = $accurate;
    }

    /**
     * Helper untuk validasi prefix kode barang yang diizinkan.
     */
    protected function isAllowedAssetCode(?string $accurateNo): bool
    {
        return !empty($accurateNo);
    }

    /**
     * FULL SYNC / SINGLE SYNC ROUTER
     */
    public function syncFromAccurate($specificId = null, bool $fresh = false): array
    {
        $start = microtime(true);

        if ($specificId) {
            $item = $this->accurate->getSingleFixedAssetDetail($specificId);

            if (!$item) {
                return ['success' => false, 'message' => 'Tidak ada data untuk diproses'];
            }

            $counters = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'failed' => 0];
            $this->processSingleItem($item, $counters);

            return [
                'success'                 => true,
                'message'                 => 'Sync single item selesai.',
                'created_assets_count'    => $counters['created'],
                'updated_assets_count'    => $counters['updated'],
                'skipped_no_change_count' => $counters['skipped'],
                'failed_count'            => $counters['failed'],
                'execution_time'          => round(microtime(true) - $start, 2) . ' seconds',
            ];
        }

        $counters = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'failed' => 0];

        $streamResult = $this->accurate->syncAllFixedAssets(
            function (array $item) use (&$counters) {
                $this->processSingleItem($item, $counters);
            },
            $fresh
        );

        if ($streamResult['items_fetched'] === 0 && $streamResult['items_failed'] === 0) {
            return ['success' => false, 'message' => 'Tidak ada data untuk diproses'];
        }

        return [
            'success'                 => true,
            'message'                 => 'Sync selesai (Streaming + Resumable + Batch Multi-QTY + Hash Skip).',
            'pages_fetched'           => $streamResult['pages_fetched'],
            'items_fetched_from_api'  => $streamResult['items_fetched'],
            'items_failed_from_api'   => $streamResult['items_failed'],
            'created_assets_count'    => $counters['created'],
            'updated_assets_count'    => $counters['updated'],
            'skipped_no_change_count' => $counters['skipped'],
            'failed_count'            => $counters['failed'],
            'execution_time'          => round(microtime(true) - $start, 2) . ' seconds',
        ];
    }

    /**
     * Proses 1 item hasil dari Accurate dengan Transaction per-item.
     */
    protected function processSingleItem(array $item, array &$counters): void
    {
        $accurateId = $item['id'] ?? null;
        $accurateNo = $item['number'] ?? null;

        if (!$accurateId) {
            return;
        }

        try {
            DB::beginTransaction();

            $existings = Asset::where(function ($query) use ($accurateId, $accurateNo) {
                $query->where('accurate_fixed_asset_id', $accurateId)
                    ->orWhere('accurate_item_id', $accurateId);

                if (!empty($accurateNo)) {
                    $query->orWhere('accurate_no', $accurateNo);
                }
            })->get();

            if ($existings->isNotEmpty()) {
                $result = $this->applyUpdateBatch($existings, $item);
                if ($result['skipped'] ?? false) {
                    $counters['skipped'] += $existings->count();
                } else {
                    $counters['updated'] += $existings->count();
                }
            } else {
                $this->applyInsert($item);
                $counters['created'] += max(1, (int) ($item['quantity'] ?? 1));
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $counters['failed']++;
            logger()->error('SYNC ITEM ERROR', [
                'accurate_id' => $accurateId,
                'message'     => $e->getMessage(),
                'line'        => $e->getLine(),
            ]);
        }
    }

    /**
     * Bangun snapshot hash dari field sumber Accurate.
     */
    protected function buildAccurateHash(array $item): string
    {
        $qty = max(1, (int) ($item['quantity'] ?? 1));
        $resolvedName = $item['name'] ?? $item['description'] ?? $item['notes'] ?? null;

        $signature = [
            'name'           => $resolvedName,
            'book_value'     => round(($item['bookValue'] ?? 0) / $qty, 2),
            'purchase_price' => round(($item['assetCost'] ?? 0) / $qty, 2),
            'depreciation'   => round(($item['depreciationAmount'] ?? 0) / $qty, 2),
            'useful_life'    => $item['estimatedLife'] ?? null,
        ];

        return hash('sha256', json_encode($signature));
    }

    /**
     * SYNC SINGLE ASSET (Webhook / Manual Trigger Single)
     */
    public function syncSingleAsset(string $accurateId, array $extraData = []): array
    {
        $item = $this->accurate->getSingleFixedAssetDetail($accurateId);

        if (!$item) {
            return ['success' => false, 'message' => "Detail aset {$accurateId} tidak ditemukan di Accurate"];
        }

        $accurateNo = $item['number']
            ?? $extraData['no']
            ?? $extraData['itemNo']
            ?? $extraData['number']
            ?? data_get($item, 'raw.no')
            ?? data_get($item, 'raw.itemNo')
            ?? data_get($item, 'raw.number')
            ?? null;

        $item['number'] = $accurateNo;

        if (!$this->isAllowedAssetCode($accurateNo)) {
            return [
                'success' => false,
                'skipped' => true,
                'message' => "Kode aset '{$accurateNo}' tidak masuk dalam daftar yang diizinkan, diabaikan"
            ];
        }

        $existings = Asset::where(function ($query) use ($accurateId, $accurateNo) {
            $query->where('accurate_fixed_asset_id', $accurateId)
                ->orWhere('accurate_item_id', $accurateId);

            if (!empty($accurateNo)) {
                $query->orWhere('accurate_no', $accurateNo);
            }
        })->get();

        if ($existings->isNotEmpty()) {
            foreach ($existings as $existing) {
                if (empty($existing->accurate_item_id)) {
                    $existing->accurate_item_id = $accurateId;
                }
                if (empty($existing->accurate_fixed_asset_id) && !empty($item['id'])) {
                    $existing->accurate_fixed_asset_id = $item['id'];
                }
                $existing->save();
            }

            return $this->applyUpdateBatch($existings, $item);
        }

        return $this->applyInsert($item);
    }

    /**
     * Method Update Massal untuk Multiple-QTY Items dengan Hash Skip
     */
    protected function applyUpdateBatch($existings, array $item): array
    {
        $newHash = $this->buildAccurateHash($item);
        $qty = max(1, (int) ($item['quantity'] ?? 1));

        $updatedCount = 0;
        $skippedCount = 0;

        foreach ($existings as $existing) {
            if ($existing->accurate_sync_hash === $newHash) {
                $skippedCount++;
                continue;
            }

            $updatedName = $item['name'] ?? $item['description'] ?? $item['notes'] ?? $existing->name;

            $existing->update([
                'name'                     => $updatedName,
                'accurate_name'            => $updatedName,
                'description'              => $item['description'] ?? $item['notes'] ?? $existing->description,
                'purchase_price'           => round(($item['assetCost'] ?? $item['purchasePrice'] ?? 0) / $qty, 2),
                'total_price'              => round(($item['assetCost'] ?? $item['purchasePrice'] ?? 0) / $qty, 2),
                'book_value'               => round(($item['bookValue'] ?? 0) / $qty, 2),
                'accumulated_depreciation' => round(($item['depreciationAmount'] ?? 0) / $qty, 2),
                'useful_life_month'        => $item['estimatedLife'] ?? $existing->useful_life_month,
                'accurate_no'              => $item['number'] ?? $existing->accurate_no,
                'accurate_last_update'     => now(),
                'last_synced_at'           => now(),
                'accurate_raw_json'        => json_encode($item['raw'] ?? $item),
                'accurate_sync_hash'       => $newHash,
            ]);

            $updatedCount++;
        }

        if ($updatedCount === 0 && $skippedCount > 0) {
            return ['success' => true, 'skipped' => true, 'message' => 'Data Accurate tidak berubah (Skipped)'];
        }

        return ['success' => true, 'skipped' => false, 'message' => "Sebanyak {$updatedCount} item aset berhasil diperbarui dari Accurate"];
    }

    /**
     * Memproses Insert Baru
     */
    protected function applyInsert(array $item): array
    {
        $qty     = max(1, (int) ($item['quantity'] ?? 1));
        $newHash = $this->buildAccurateHash($item);

        for ($i = 0; $i < $qty; $i++) {
            Asset::create([
                'asset_number'             => $this->generateAssetNumber(),
                'name'                     => $item['name'] ?? 'Unknown Asset',
                'location_id'              => null,
                'purchase_date'            => $this->parseDate($item['purchaseDate'] ?? null),
                'quantity'                 => 1,
                'purchase_price'           => round(($item['assetCost'] ?? 0) / $qty, 2),
                'total_price'              => round(($item['assetCost'] ?? 0) / $qty, 2),
                'book_value'               => round(($item['bookValue'] ?? 0) / $qty, 2),
                'residual_value'           => 0,
                'accumulated_depreciation' => round(($item['depreciationAmount'] ?? 0) / $qty, 2),
                'useful_life_month'        => $item['estimatedLife'] ?? null,
                'status'                   => 'draft',
                'qr_token'                 => (string) Str::uuid(),

                'accurate_fixed_asset_id'  => $item['id'],
                'accurate_item_id'         => $item['itemId'] ?? $item['id'],
                'accurate_name'            => $item['name'] ?? null,
                'accurate_no'              => $item['number'] ?? null,
                'accurate_item_type'       => 'fixed_asset',

                'is_synced'                => true,
                'from_accurate'            => true,
                'auto_sync'                => true,
                'last_synced_at'           => now(),
                'accurate_last_update'     => now(),
                'accurate_raw_json'        => json_encode($item['raw'] ?? $item),
                'accurate_sync_hash'       => $newHash,
            ]);
        }

        return ['success' => true, 'skipped' => false, 'message' => 'Aset baru ditambahkan dari Accurate'];
    }

    /**
     * Safe Date Parsing Helper
     */
    protected function parseDate(?string $dateStr): ?string
    {
        if (empty($dateStr)) {
            return null;
        }

        try {
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dateStr)) {
                return Carbon::createFromFormat('d/m/Y', $dateStr)->format('Y-m-d');
            }
            return Carbon::parse($dateStr)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function generateAssetNumber(): string
    {
        return 'AST-' . strtoupper(Str::random(10));
    }
}