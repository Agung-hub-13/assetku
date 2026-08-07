<?php

namespace App\Jobs;

use App\Services\AssetSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncAllAssetsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 900; // 15 menit
    public int $tries = 3;

    public function handle(AssetSyncService $service): void
    {
        $service->syncFromAccurate();
    }

    public function failed(\Throwable $e): void
    {
        logger()->error('MASS ASSET SYNC FAILED', ['error' => $e->getMessage()]);
    }
}