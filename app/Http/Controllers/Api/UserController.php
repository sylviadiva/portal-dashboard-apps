<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return response()->json(
            User::with(['department', 'role'])->orderBy('name')->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'role_id' => ['nullable', 'exists:roles,id'],
            'is_admin' => ['boolean'],
        ]);

        $validated['password'] = bcrypt('password');
        $validated['is_change_default_password'] = true;

        $user = User::create($validated);

        return response()->json($user->load(['department', 'role']), 201);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'department_id' => ['nullable', 'exists:departments,id'],
            'role_id' => ['nullable', 'exists:roles,id'],
            'is_admin' => ['boolean'],
            'reset_password' => ['boolean'],
        ]);

        if (!empty($validated['reset_password'])) {
            $validated['password'] = bcrypt('password');
            $validated['is_change_default_password'] = true;
        }
        unset($validated['reset_password']);

        $user->update($validated);

        return response()->json($user->load(['department', 'role']));
    }

    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(['message' => 'User berhasil dihapus']);
    }
}