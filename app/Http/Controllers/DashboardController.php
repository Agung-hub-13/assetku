<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Building;
use App\Models\Asset;
use App\Models\WorkOrder;

class DashboardController extends Controller
{
    public function admin()
    {
        return view('admin.dashboard', [
            'branchCount' => Branch::count(),
            'buildingCount' => Building::count(),
            'assetCount' => Asset::count(),
            'openWorkOrder' => WorkOrder::where('status','open')->count(),
        ]);
    }

    public function mobile()
    {
        return view('mobile.dashboard');
    }
}
