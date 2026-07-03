<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AccessService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private AccessService $accessService)
    {
        
    }

    /**
     * Daftar aplikasi yang bisa diakses user yang sedang login.
     * Ini yang dipanggil dashboard user (requirement 3A).
     */
    public function myApplications(Request $request)
    {
        $applications = $this->accessService->getEffectiveApplications($request->user());

        return response()->json($applications);
    }

    /**
     * Effective access SEMUA user sekaligus, untuk halaman admin
     * "Database View - Effective Access" (requirement 3D).
     */
    public function effectiveAccessMatrix()
    {
        $matrix = $this->accessService->getEffectiveAccessMatrix();

        return response()->json($matrix);
    }
}