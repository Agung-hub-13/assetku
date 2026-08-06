<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AssetSyncService;

class SyncAccurateAssetCommand extends Command
{
    protected $signature = 'accurate:sync-asset {--id= : Sync satu ID Accurate saja (opsional)} {--max-attempts=20 : Maksimal percobaan ulang otomatis}';

    protected $description = 'Full sync asset dari Accurate — otomatis retry sampai benar-benar tuntas (checkpoint resume antar percobaan)';

    public function handle(AssetSyncService $service)
    {
        $specificId = $this->option('id');
        $maxAttempts = (int) $this->option('max-attempts');

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $this->info("=== PERCOBAAN KE-{$attempt}/{$maxAttempts} ===");

            try {
                $result = $service->syncFromAccurate($specificId);

                if (!($result['success'] ?? false)) {
                    $this->error($result['message'] ?? 'Sync gagal tanpa pesan.');
                    return self::FAILURE;
                }

                $this->info('=================================');
                $this->info('SYNC ACCURATE SELESAI TUNTAS');
                $this->info('Created  : ' . ($result['created_assets_count'] ?? 0));
                $this->info('Updated  : ' . ($result['updated_assets_count'] ?? 0));
                $this->info('Skipped  : ' . ($result['skipped_no_change_count'] ?? 0));
                $this->info('Failed   : ' . ($result['failed_count'] ?? 0));
                $this->info('Pages    : ' . ($result['pages_fetched'] ?? '-'));
                $this->info('Time     : ' . ($result['execution_time'] ?? '-'));
                $this->info('=================================');

                return self::SUCCESS;
            } catch (\Exception $e) {
                $this->error("Percobaan {$attempt} gagal: " . $e->getMessage());

                if ($attempt >= $maxAttempts) {
                    $this->error('Sudah mencapai batas maksimal percobaan. Berhenti.');
                    return self::FAILURE;
                }

                $waitSeconds = min(60, $attempt * 10);
                $this->warn("Menunggu {$waitSeconds} detik sebelum lanjut otomatis dari checkpoint terakhir...");
                sleep($waitSeconds);
            }
        }

        return self::FAILURE;
    }
}