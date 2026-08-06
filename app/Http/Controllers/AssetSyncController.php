<?php

namespace App\Http\Controllers;

use App\Services\AssetSyncService;

class AssetSyncController extends Controller
{
    protected $sync;

    public function __construct(AssetSyncService $sync)
    {
        $this->sync = $sync;
    }

    public function sync()
    {
        return response()->json(
            $this->sync->syncFromAccurate()
        );
    }
}