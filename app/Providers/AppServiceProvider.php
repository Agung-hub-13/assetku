<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Gate;

use App\Models\AssetMaintenance;
use App\Models\AssetLoan;
use App\Models\AssetTransfer;
// use App\Models\AssetMutation;
// use App\Models\AssetDepreciation;

use App\Observers\AssetMaintenanceObserver;
use App\Observers\AssetLoanObserver;
use App\Observers\AssetTransferObserver;
// use App\Observers\AssetMutationObserver;
// use App\Observers\AssetDepreciationObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // 1. Paginator Tailwind
        Paginator::useTailwind();

        // 2. Paksa HTTPS di production (sisa kondisi ngrok sudah dihapus, tidak relevan lagi di server)
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // 3. Super Admin Gate Bypass
        Gate::before(function ($user, $ability) {
            return ($user->hasRole('Super Admin') || $user->hasRole('super-admin')) ? true : null;
        });

        // 4. Registrasi Observers
        AssetMaintenance::observe(AssetMaintenanceObserver::class);
        AssetLoan::observe(AssetLoanObserver::class);
        AssetTransfer::observe(AssetTransferObserver::class);

        // AssetMutation::observe(AssetMutationObserver::class);
        // AssetDepreciation::observe(AssetDepreciationObserver::class);
    }
}