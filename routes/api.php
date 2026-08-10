<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssetSyncController;
use App\Http\Controllers\AccurateTokenController;
use App\Http\Controllers\AccurateWebhookController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AssetController;

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