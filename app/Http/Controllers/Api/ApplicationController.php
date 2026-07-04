<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function index()
    {
        return response()->json(
            Application::orderBy('name')->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:20'],
        ]);

        $application = Application::create($validated);

        return response()->json($application, 201);
    }

    public function show(Application $application)
    {
        return response()->json(
            $application->load(['departments', 'roles', 'users'])
        );
    }

    public function update(Request $request, Application $application)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:20'],
        ]);

        $application->update($validated);

        return response()->json($application);
    }

    public function destroy(Application $application)
    {
        // Rule akses di app_department/app_role/app_user otomatis ikut terhapus
        // karena foreign key-nya pakai cascadeOnDelete() di migration.
        $application->delete();

        return response()->json(['message' => 'Aplikasi berhasil dihapus']);
    }
}