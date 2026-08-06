<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetLocation;
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
        if (!$accurateNo) {
            return false;
        }

        $allowedPrefixes = ['FAA', '9200', 'AST'];
        $upperNo = strtoupper($accurateNo);

        foreach ($allowedPrefixes as $prefix) {
            if (Str::startsWith($upperNo, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * FULL SYNC
     */
    public function syncFromAccurate($specificId = null)
    {
        $start = microtime(true);

        $items = $specificId
            ? array_filter([$this->accurate->getSingleFixedAssetDetail($specificId)])
            : $this->accurate->getAllFixedAssets();

        if (empty($items)) {
            return ['success' => false, 'message' => 'Tidak ada data untuk diproses'];
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($items as $item) {
            $accurateId = $item['id'] ?? null;
            $accurateNo = $item['number'] ?? null;

            if (!$accurateId || !$this->isAllowedAssetCode($accurateNo)) {
                continue;
            }

            // 🔧 Transaction per-item — 1 item gagal tidak lagi rollback item lain yang sudah sukses
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
                        $skipped += $existings->count();
                    } else {
                        $updated += $existings->count();
                    }
                } else {
                    $this->applyInsert($item);
                    $created += max(1, (int)($item['quantity'] ?? 1));
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $failed++;
                logger()->error('SYNC ITEM ERROR', [
                    'accurate_id' => $accurateId,
                    'message'     => $e->getMessage(),
                    'line'        => $e->getLine(),
                ]);
                continue; // lanjut ke item berikutnya, jangan hentikan seluruh proses
            }
        }

        return [
            'success' => true,
            'message' => 'Sync selesai (Upsert Mode + Batch Multi-QTY + Hash Skip).',
            'created_assets_count'    => $created,
            'updated_assets_count'    => $updated,
            'skipped_no_change_count' => $skipped,
            'failed_count'            => $failed,
            'execution_time'          => round(microtime(true) - $start, 2) . ' seconds',
        ];
    }

    /**
     * Bangun snapshot hash HANYA dari field yang sumbernya Accurate.
     */
    protected function buildAccurateHash(array $item): string
    {
        $qty = max(1, (int)($item['quantity'] ?? 1));
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

        // 💡 Ambil SELURUH baris aset terkait ID Accurate ini
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

            // Update massal ke semua pecahan item QTY
            return $this->applyUpdateBatch($existings, $item);
        }

        return $this->applyInsert($item);
    }

    /**
     * Method Update Massal untuk Multiple-QTY Items
     */
    protected function applyUpdateBatch($existings, array $item): array
    {
        $newHash = $this->buildAccurateHash($item);
        $qty = max(1, (int)($item['quantity'] ?? 1));

        $updatedCount = 0;
        $skippedCount = 0;

        foreach ($existings as $existing) {
            // Jika hash tidak berubah, skip update ke DB
            if ($existing->accurate_sync_hash === $newHash) {
                $skippedCount++;
                continue;
            }

            $updatedName = $item['name'] ?? $item['description'] ?? $item['notes'] ?? $existing->name;

            // Update field dari Accurate (Location_id aman dari penimpaan)
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
        $qty     = max(1, (int)($item['quantity'] ?? 1));
        $newHash = $this->buildAccurateHash($item);

        for ($i = 0; $i < $qty; $i++) {
            Asset::create([
                'asset_number'             => $this->generateAssetNumber(),
                'name'                     => $item['name'] ?? 'Unknown Asset',
                'location_id'              => null, // Menjaga null secara eksplisit untuk pengisian manual via AssetKu
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
