<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\Request;

class AccessController extends Controller
{
    public function show(Application $application)
    {
        return response()->json([
            'application' => $application,
            'departments' => $application->departments,
            'roles' => $application->roles,
            'users' => $application->users,
        ]);
    }

    public function syncDepartments(Request $request, Application $application)
    {
        $validated = $request->validate([
            'department_ids' => ['array'],
            'department_ids.*' => ['integer', 'exists:departments,id'],
        ]);

        $application->departments()->sync($validated['department_ids'] ?? []);

        return response()->json($application->load('departments'));
    }

    public function syncRoles(Request $request, Application $application)
    {
        $validated = $request->validate([
            'role_ids' => ['array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
        ]);

        $application->roles()->sync($validated['role_ids'] ?? []);

        return response()->json($application->load('roles'));
    }

    public function syncUsers(Request $request, Application $application)
    {
        $validated = $request->validate([
            'user_ids' => ['array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $application->users()->sync($validated['user_ids'] ?? []);

        return response()->json($application->load('users'));
    }
}