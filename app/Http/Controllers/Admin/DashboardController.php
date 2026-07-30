<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminStatsService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(AdminStatsService $stats): View
    {
        return view('admin.dashboard', $stats->dashboard());
    }
}
