<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SectorCompanyRepository;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(SectorCompanyRepository $companies): View
    {
        $sectorStats = $companies->dashboardStats();
        $totalRecords = array_sum(array_column($sectorStats, 'total'));

        return view('admin.dashboard', [
            'usersCount' => User::query()->count(),
            'adminsCount' => User::query()->where('is_admin', true)->count(),
            'sectorsCount' => count($sectorStats),
            'totalRecords' => $totalRecords,
            'sectorStats' => $sectorStats,
            'plans' => config('plans', []),
            'users' => User::query()->orderByDesc('is_admin')->orderBy('name')->get(),
        ]);
    }
}
