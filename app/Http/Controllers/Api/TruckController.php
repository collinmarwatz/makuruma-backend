<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Truck;
use Illuminate\Http\Request;

class TruckController extends Controller
{
    public function index()
    {
        return Truck::with(['documents', 'trailer', 'driver'])->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reg_no' => 'required|string|unique:trucks,reg_no',
            'capacity' => 'required|numeric',
            'status' => 'in:active,maintenance,decommissioned',
            'trailer_id' => 'nullable|exists:trailers,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'buying_price' => 'nullable|numeric',
            'trip_status' => 'nullable|in:go,return,off_duty',
        ]);

        $truck = Truck::create($validated);

        return response()->json($truck->load(['documents', 'trailer', 'driver']), 201);
    }

    public function show(Truck $truck)
    {
        return $truck->load(['documents', 'trailer', 'driver']);
    }

    public function update(Request $request, Truck $truck)
    {
        $validated = $request->validate([
            'reg_no' => 'sometimes|required|string|unique:trucks,reg_no,' . $truck->id,
            'capacity' => 'sometimes|required|numeric',
            'status' => 'in:active,maintenance,decommissioned',
            'trailer_id' => 'nullable|exists:trailers,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'buying_price' => 'nullable|numeric',
            'trip_status' => 'nullable|in:go,return,off_duty',
        ]);

        $truck->update($validated);

        return $truck->load(['documents', 'trailer', 'driver']);
    }
}