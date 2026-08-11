<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssetSyncController;
use App\Http\Controllers\AccurateTokenController;
use App\Http\Controllers\AccurateWebhookController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AssetController;
use App\Http\Controllers\Api\AssetMaintenanceController;
use App\Http\Controllers\Api\AssetLoanController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\AssetTransferController;

Route::get('/test-accurate', [AccurateTokenController::class, 'testAccurate']);
Route::get('/test-detail', [AccurateTokenController::class, 'testDetail']);
Route::post('/accurate/sync-assets', [AssetSyncController::class, 'sync']);
Route::post('/accurate/webhook', [AccurateWebhookController::class, 'handle']);

Route::post('/login', [AuthController::class, 'login']);

Route::get('/assets', [AssetController::class, 'index']);
Route::post('/assets', [AssetController::class, 'store']);

// TAMBAHKAN ROUTE INI untuk Dropdown Filter
Route::get('/asset-locations', [AssetController::class, 'getLocations']); // Sesuaikan dengan nama method di AssetController Anda
Route::get('/asset-categories', [AssetController::class, 'getCategories']); // Sesuaikan dengan nama method di AssetController Anda

Route::get('/asset-maintenance', [AssetMaintenanceController::class, 'index']);
Route::post('/asset-maintenance', [AssetMaintenanceController::class, 'store']);

Route::get('/asset-loans', [AssetLoanController::class, 'index']);
Route::post('/asset-loans', [AssetLoanController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
});

Route::get('/asset-transfers', [AssetTransferController::class, 'index']);
Route::post('/asset-transfers', [AssetTransferController::class, 'store']);
