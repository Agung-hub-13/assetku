<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    UserController,
    RoleController,
    RolePermissionController,
    ProfileController,
    AssetCategoryController,
    AssetLocationController,
    AssetController,
    AssetTransferController,
    AccurateTokenController,
    AssetDashboardController,
    AssetAttachmentController,
    AssetLoanController,
    AssetMaintenanceController,
    AssetLogController,
    NotificationController,
    AssetReportController,
    AssetAuditController,
    DepartmentController
};

/*
|--------------------------------------------------------------------------
| Public Routes & Root Redirection
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Public Scanner Route
Route::get('scan-asset/{qr_token}', [AssetController::class, 'publicPreview'])->name('assets.public-preview');

// Accurate Integration OAuth Routes
Route::get('/accurate/connect', [AccurateTokenController::class, 'redirectToAccurate']);
Route::get('/accurate/callback', [AccurateTokenController::class, 'handleCallback']);

// Authenticated Root Dashboard Redirection
Route::middleware(['auth', 'verified'])->get('/dashboard', function () {
    $user = auth()->user();

    if ($user->hasRole('Super Admin') || $user->hasRole('super-admin')) {
        return redirect()->route('admin.dashboard');
    }

    if ($user->hasPermissionTo('access-mobile') && !$user->hasPermissionTo('access-desktop')) {
        return redirect()->route('mobile.dashboard');
    }

    if ($user->hasPermissionTo('access-desktop')) {
        return redirect()->route('admin.dashboard');
    }

    \Illuminate\Support\Facades\Auth::logout();

    return redirect()->route('login')->withErrors([
        'email' => 'Akun Anda belum dikonfigurasi. Silakan hubungi Administrator untuk mengaktifkan akses platform Anda.'
    ]);
})->name('dashboard');

/*
|--------------------------------------------------------------------------
| Admin Desktop Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'platform.restrict:desktop', 'permission:access-desktop']) // 🔒 Proteksi Desktop & Permission
    ->group(function () {

        Route::get('/dashboard', [AssetDashboardController::class, 'index'])->name('dashboard');
        Route::get('/api/dashboard-assets', [AssetDashboardController::class, 'getDashboardData']);

        // Profile Routes (Dapat diakses seluruh pengguna desktop)
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        // -------------------------------------------------------------------
        // Modul Aset (Static Routes diletakkan sebelum Dynamic Parameters)
        // -------------------------------------------------------------------
        Route::get('assets/export-excel', [AssetController::class, 'exportExcel'])
            ->middleware('permission:asset.view')
            ->name('assets.export-excel');

        Route::get('assets/locations-by-department-name/{department}', [AssetController::class, 'locationsByDepartmentName'])
            ->middleware('permission:asset.view')
            ->name('assets.locations-by-department-name');

        Route::post('/assets/bulk-assign', [AssetController::class, 'bulkAssign'])
            ->middleware('permission:asset.edit')
            ->name('assets.bulkAssign');

        Route::post('assets/bulk-print-qr', [AssetController::class, 'bulkPrintQr'])
            ->middleware('permission:asset.view')
            ->name('assets.bulkPrintQr');

        Route::post('/assets/sync-all', [AssetController::class, 'syncAll'])
            ->middleware('permission:asset.edit')
            ->name('assets.sync-all');

        // Asset Routes dengan Parameter Dinamis
        Route::post('/assets/{id}/sync', [AssetController::class, 'syncSingle'])
            ->whereNumber('id')
            ->middleware('permission:asset.edit')
            ->name('assets.sync-single');

        Route::get('assets/{id}/print-qrcode', [AssetController::class, 'printQrCode'])
            ->whereNumber('id')
            ->middleware('permission:asset.view')
            ->name('assets.print-qrcode');

        Route::get('assets/{asset}', [AssetController::class, 'show'])
            ->whereNumber('asset')
            ->middleware('permission:asset.view')
            ->name('assets.show');

        // Resource Asset Utama
        Route::resource('assets', AssetController::class)
            ->except(['show'])
            ->whereNumber('asset');

        // -------------------------------------------------------------------
        // Asset Logs / Audit Trail
        // -------------------------------------------------------------------
        Route::prefix('asset-logs')->name('asset_logs.')->middleware('permission:asset.view')->group(function () {
            Route::get('/', [AssetLogController::class, 'index'])->name('index');
            Route::get('/asset/{asset}', [AssetLogController::class, 'showByAsset'])
                ->whereNumber('asset')
                ->name('by_asset');
            Route::get('/{assetLog}', [AssetLogController::class, 'show'])
                ->whereNumber('assetLog')
                ->name('show');
            Route::post('/', [AssetLogController::class, 'store'])->name('store');
            Route::delete('/{assetLog}', [AssetLogController::class, 'destroy'])
                ->whereNumber('assetLog')
                ->middleware('permission:asset.delete')
                ->name('destroy');
        });

        // -------------------------------------------------------------------
        // Laporan Asset
        // -------------------------------------------------------------------
        Route::prefix('asset-reports')->name('asset_reports.')->middleware('permission:asset.view')->group(function () {
            Route::get('/', [AssetReportController::class, 'index'])->name('index');
            Route::get('/maintenance', [AssetReportController::class, 'maintenanceReport'])->name('maintenance');
            Route::get('/loans', [AssetReportController::class, 'loanReport'])->name('loans');
        });

        // -------------------------------------------------------------------
        // Asset Audits / Stock Opname
        // -------------------------------------------------------------------
        // -------------------------------------------------------------------
        // Asset Audits / Stock Opname
        // -------------------------------------------------------------------
        Route::prefix('asset-audits')->name('asset_audits.')->middleware('permission:asset.view')->group(function () {
            Route::get('/', [AssetAuditController::class, 'index'])->name('index');
            Route::get('/create', [AssetAuditController::class, 'create'])->middleware('permission:asset.create')->name('create');
            Route::post('/', [AssetAuditController::class, 'store'])->middleware('permission:asset.create')->name('store');

            // ✅ Rute statis wajib diletakkan di atas rute parameter dinamis
            Route::post('/scan-qr', [AssetAuditController::class, 'scanQr'])->name('scan_qr');

            // Fast Scan Barcode / QR dengan parameter
            Route::post('/{auditCode}/scan', [AssetAuditController::class, 'scan'])->name('scan');

            Route::get('/{auditCode}', [AssetAuditController::class, 'show'])->name('show');
            Route::put('/{assetAudit}', [AssetAuditController::class, 'update'])->middleware('permission:asset.edit')->name('update');
            Route::delete('/{assetAudit}', [AssetAuditController::class, 'destroy'])->middleware('permission:asset.delete')->name('destroy');
        });

        Route::put('/asset-audit-items/{item}', [AssetAuditController::class, 'updateItem'])
            ->middleware('permission:asset.edit')
            ->name('asset_audit_items.update');

        // -------------------------------------------------------------------
        // Asset Attachments
        // -------------------------------------------------------------------
        Route::get('/assets/{asset}/attachments', [AssetAttachmentController::class, 'index'])->name('assets.attachments.index');
        Route::post('/assets/{asset}/attachments', [AssetAttachmentController::class, 'store'])->middleware('permission:asset.edit')->name('assets.attachments.store');
        Route::get('/attachments/{attachment}/download', [AssetAttachmentController::class, 'download'])->name('attachments.download');
        Route::put('/attachments/{attachment}', [AssetAttachmentController::class, 'update'])->middleware('permission:asset.edit')->name('attachments.update');
        Route::patch('/attachments/{attachment}/set-primary', [AssetAttachmentController::class, 'setPrimary'])->middleware('permission:asset.edit')->name('attachments.set-primary');
        Route::delete('/attachments/{attachment}', [AssetAttachmentController::class, 'destroy'])->middleware('permission:asset.delete')->name('attachments.destroy');

        // -------------------------------------------------------------------
        // Asset Transfers Actions (Approval & Detail)
        // -------------------------------------------------------------------
        Route::post('asset_transfers/{id}/approve', [AssetTransferController::class, 'approve'])->middleware('permission:transfer.approve')->name('asset_transfers.approve');
        Route::post('asset_transfers/{id}/reject', [AssetTransferController::class, 'reject'])->middleware('permission:transfer.approve')->name('asset_transfers.reject');
        Route::get('asset_transfers/{assetTransfer}', [AssetTransferController::class, 'show'])->middleware('permission:transfer.view')->name('asset_transfers.show');

        // -------------------------------------------------------------------
        // Asset Loans Actions
        // -------------------------------------------------------------------
        // -------------------------------------------------------------------
        // Asset Loans Actions
        // -------------------------------------------------------------------
        Route::patch('asset_loans/{assetLoan}/approve', [AssetLoanController::class, 'approve'])
            ->middleware('permission:loan.approve')
            ->name('asset_loans.approve');

        Route::post('asset_loans/{assetLoan}/reject', [AssetLoanController::class, 'reject'])
            ->middleware('permission:loan.reject')
            ->name('asset_loans.reject');

        Route::patch('asset_loans/{assetLoan}/handover', [AssetLoanController::class, 'handover'])
            ->middleware('permission:loan.handover')
            ->name('asset_loans.handover');

        Route::patch('asset_loans/{assetLoan}/return', [AssetLoanController::class, 'returnAsset'])
            ->middleware('permission:loan.return')
            ->name('asset_loans.return');

        Route::get('asset_loans/{assetLoan}', [AssetLoanController::class, 'show'])
            ->middleware('permission:loan.view')
            ->name('asset_loans.show');

        // -------------------------------------------------------------------
        // Asset Maintenances Actions
        // -------------------------------------------------------------------
        Route::patch('asset-maintenances/{maintenance}/progress', [AssetMaintenanceController::class, 'updateProgress'])
            ->whereNumber('maintenance')
            ->name('asset_maintenances.update_progress');

        Route::get('asset-maintenances/{assetMaintenance}', [AssetMaintenanceController::class, 'show'])
            ->whereNumber('assetMaintenance')
            ->name('asset_maintenances.show');

        Route::get('maintenances/{assetMaintenance}', [AssetMaintenanceController::class, 'show'])
            ->whereNumber('assetMaintenance')
            ->name('maintenances.show');

        // -------------------------------------------------------------------
        // Notifications
        // -------------------------------------------------------------------
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
        Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
        Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllRead');

        // -------------------------------------------------------------------
        // Resource Routes dengan Penyesuaian Spatie Permission
        // -------------------------------------------------------------------
        Route::resource('users', UserController::class)
            ->whereNumber('user')
            ->middleware('permission:user.view');

        Route::resource('roles', RoleController::class)
            ->whereNumber('role')
            ->middleware('permission:role.view');

        // -------------------------------------------------------------------
        // Profile To Menu / Role Permissions Matrix
        // -------------------------------------------------------------------
        Route::get('/roles-permissions', [RolePermissionController::class, 'index'])
            ->middleware('permission:role.view')
            ->name('roles.permissions');

        Route::post('/roles-permissions', [RolePermissionController::class, 'update'])
            ->middleware('permission:role.edit')
            ->name('role-permissions.update');

        Route::resources([
            'asset_categories'   => AssetCategoryController::class,
            'asset_locations'    => AssetLocationController::class,
            'asset_departments'  => DepartmentController::class,
            'asset_transfers'    => AssetTransferController::class,
            'asset_loans'        => AssetLoanController::class,
            'asset_maintenances' => AssetMaintenanceController::class,
            'accurate_tokens'    => AccurateTokenController::class
        ], ['except' => ['show']]);
    });

/*
|--------------------------------------------------------------------------
| Mobile Platform Routes
|--------------------------------------------------------------------------
*/
Route::prefix('mobile')
    ->name('mobile.')
    ->middleware(['auth', 'platform.restrict:mobile', 'permission:access-mobile']) // 🔒 Proteksi Mobile & Permission
    ->group(function () {

        Route::get('/dashboard', function () {
            return view('mobile.dashboard');
        })->name('dashboard');

        Route::get('/assets', [AssetController::class, 'index'])->name('assets.index');
        Route::get('/assets/{asset}', [AssetController::class, 'show'])->name('assets.show');

        // Locations
        Route::get('/asset-locations', [AssetLocationController::class, 'index'])->name('asset_locations.index');
        Route::post('/asset-locations', [AssetLocationController::class, 'store'])->name('asset_locations.store');
        Route::put('/asset-locations/{id}', [AssetLocationController::class, 'update'])->name('asset_locations.update');
        Route::delete('/asset-locations/{id}', [AssetLocationController::class, 'destroy'])->name('asset_locations.destroy');

        // Transfers
        Route::get('/asset-transfers', [AssetTransferController::class, 'index'])->name('asset_transfers.index');
        Route::post('/asset-transfers', [AssetTransferController::class, 'store'])->name('asset_transfers.store');
        Route::put('/asset-transfers/{id}', [AssetTransferController::class, 'update'])->name('asset_transfers.update');
        Route::delete('/asset-transfers/{id}', [AssetTransferController::class, 'destroy'])->name('asset_transfers.destroy');
        Route::post('asset_transfers/{id}/approve', [AssetTransferController::class, 'approve'])->middleware('permission:transfer.approve')->name('asset_transfers.approve');
        Route::post('asset_transfers/{id}/reject', [AssetTransferController::class, 'reject'])->middleware('permission:transfer.approve')->name('asset_transfers.reject');

        // Loans (Mobile)
        Route::get('/asset-loans', [AssetLoanController::class, 'index'])->name('asset_loans.index');
        Route::post('/asset-loans', [AssetLoanController::class, 'store'])->name('asset_loans.store');
        Route::post('/asset-loans/{loan}/return', [AssetLoanController::class, 'returnAsset'])->name('asset_loans.return');

        // Maintenances (Mobile)
        Route::get('/asset-maintenances', [AssetMaintenanceController::class, 'index'])->name('asset_maintenances.index');
        Route::post('/asset-maintenances', [AssetMaintenanceController::class, 'store'])->name('asset_maintenances.store');
        Route::patch('/asset-maintenances/{maintenance}/progress', [AssetMaintenanceController::class, 'updateProgress'])->name('asset_maintenances.update_progress');

        // Users
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    });

require __DIR__ . '/auth.php';
