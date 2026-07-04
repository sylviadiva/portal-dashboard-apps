<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;

class ReferenceController extends Controller
{
    /**
     * Data referensi (departemen, role, user) untuk keperluan
     * dropdown/checkbox di halaman admin pengaturan akses.
     */
    public function index()
    {
        return response()->json([
            'departments' => Department::orderBy('name')->get(),
            'roles' => Role::orderBy('name')->get(),
            'users' => User::orderBy('name')->select('id', 'name', 'email')->get(),
        ]);
    }
}