<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AssetSyncService;

class SyncAccurateAssetCommand extends Command
{
    protected $signature = 'accurate:sync-asset {--id= : Sync satu ID Accurate saja (opsional)}';

    // 🔧 Deskripsi diperjelas — ini command manual, TIDAK berjalan otomatis tiap menit.
    // Jalur otomatis yang aktif ada di routes/console.php (webhook real-time + daily 01:00 safety net).
    protected $description = 'Manual trigger full sync asset dari Accurate (untuk debug/backfill, tidak dijadwalkan otomatis)';

    public function handle(AssetSyncService $service)
    {
        $result = $service->syncFromAccurate($this->option('id'));

        if (!$result['success']) {
            $this->error($result['message']);
            return self::FAILURE;
        }

        $this->info('=================================');
        $this->info('SYNC ACCURATE BERHASIL');
        $this->info('Created  : ' . ($result['created_assets_count'] ?? 0));
        $this->info('Updated  : ' . ($result['updated_assets_count'] ?? 0));
        $this->info('Skipped  : ' . ($result['skipped_no_change_count'] ?? 0));
        $this->info('Failed   : ' . ($result['failed_count'] ?? 0));
        $this->info('Time     : ' . ($result['execution_time'] ?? '-'));
        $this->info('=================================');

        return self::SUCCESS;
    }
}