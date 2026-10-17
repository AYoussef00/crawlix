<?php

namespace App\Http\Controllers;

use App\Services\SectorCompanyRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(SectorCompanyRepository $companies): View
    {
        return view('dashboard', [
            'stats' => $companies->dashboardStats(),
        ]);
    }

    public function stats(SectorCompanyRepository $companies): JsonResponse
    {
        $stats = $companies->dashboardStats();

        return response()->json([
            'sectors' => $stats,
            'total' => array_sum(array_column($stats, 'total')),
            'updated_at' => now()->toIso8601String(),
        ]);
    }
}
