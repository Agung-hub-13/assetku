<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\AssetSyncService;
use App\Services\LocationSyncService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sync Full Asset setiap malam jam 01:00 (Safety backup jika webhook miss)
Schedule::call(function () {
    app(AssetSyncService::class)->syncFromAccurate();
})
->name('sync-assets-accurate') // 👈 Tambahkan nama event di sini
->dailyAt('01:00')
->withoutOverlapping();

// Sync Gudang dan Departemen setiap minggu sekali (Senin jam 02:00)
Schedule::call(function () {
    app(LocationSyncService::class)->syncLocationsFromAccurate();
})
->name('sync-locations-accurate') // 👈 Tambahkan nama event di sini
->weeklyOn(1, '02:00')
->withoutOverlapping();

Schedule::command('maintenance:send-reminders')->dailyAt('08:00');
Schedule::command('loan:send-reminders')->dailyAt('08:00');