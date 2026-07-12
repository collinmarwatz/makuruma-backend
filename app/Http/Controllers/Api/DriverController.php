<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function index()
    {
        return Driver::with('documents')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string',
            'phone' => 'nullable|string',
        ]);

        $driver = Driver::create($validated);

        return response()->json($driver->load('documents'), 201);
    }

    public function show(Driver $driver)
    {
        return $driver->load('documents');
    }

    public function update(Request $request, Driver $driver)
    {
        $validated = $request->validate([
            'full_name' => 'sometimes|required|string',
            'phone' => 'nullable|string',
        ]);

        $driver->update($validated);

        return $driver->load('documents');
    }

    public function destroy(Driver $driver)
    {
        $driver->delete();

        return response()->json(null, 204);
    }
}