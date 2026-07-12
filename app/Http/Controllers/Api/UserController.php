<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return User::with('role')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'phone' => 'nullable|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role_id' => 'nullable|exists:roles,id',
            'status' => 'in:active,suspended',
        ]);

        $user = User::create($validated);

        return response()->json($user->load('role'), 201);
    }

    public function show(User $user)
    {
        return $user->load('role');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string',
            'phone' => 'nullable|string',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'role_id' => 'nullable|exists:roles,id',
            'status' => 'in:active,suspended',
        ]);

        // Don't overwrite the password with an empty string if left blank
        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);

        return $user->load('role');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(null, 204);
    }
}