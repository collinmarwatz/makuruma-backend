<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function index()
    {
        return Staff::with('documents')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string',
            'phone' => 'nullable|string',
            'tin_number' => 'nullable|string',
            'payment_account' => 'nullable|string',
        ]);

        $staff = Staff::create($validated);

        return response()->json($staff->load('documents'), 201);
    }

    public function show(Staff $staff)
    {
        return $staff->load('documents');
    }

    public function update(Request $request, Staff $staff)
    {
        $validated = $request->validate([
            'full_name' => 'sometimes|required|string',
            'phone' => 'nullable|string',
            'tin_number' => 'nullable|string',
            'payment_account' => 'nullable|string',
        ]);

        $staff->update($validated);

        return $staff->load('documents');
    }

    public function destroy(Staff $staff)
    {
        $staff->delete();

        return response()->json(null, 204);
    }
}