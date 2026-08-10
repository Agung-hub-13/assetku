<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssetSyncController;
use App\Http\Controllers\AccurateTokenController;
use App\Http\Controllers\AccurateWebhookController;
use App\Http\Controllers\Api\AuthController;

Route::get('/test-accurate', [AccurateTokenController::class, 'testAccurate']);
Route::get('/test-detail', [AccurateTokenController::class, 'testDetail']);
Route::post('/accurate/sync-assets', [AssetSyncController::class, 'sync']);
Route::post('/accurate/webhook', [AccurateWebhookController::class, 'handle']);

Route::post('/login', [AuthController::class, 'login']);
Route::get('/assets', [AssetController::class, 'index']);
Route::post('/assets', [AssetController::class, 'store']);