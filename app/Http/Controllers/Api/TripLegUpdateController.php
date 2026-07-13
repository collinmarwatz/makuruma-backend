<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\TripLeg;
use Illuminate\Http\Request;

class TripLegController extends Controller
{
    public function store(Request $request, Trip $trip)
    {
        $validated = $request->validate([
            'direction' => 'required|in:go,return',
            'client_id' => 'nullable|exists:clients,id',
            'rate' => 'nullable|numeric',
            'eta' => 'nullable|date',
            'location' => 'nullable|string',
            'item_sn' => 'nullable|string',
            'description' => 'nullable|string',
            'quantity' => 'nullable|numeric',
            'amount' => 'nullable|numeric',
        ]);

        $leg = $trip->legs()->create($validated);

        return response()->json($leg->load('client'), 201);
    }

    public function update(Request $request, TripLeg $leg)
    {
        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'rate' => 'nullable|numeric',
            'eta' => 'nullable|date',
            'location' => 'nullable|string',
            'item_sn' => 'nullable|string',
            'description' => 'nullable|string',
            'quantity' => 'nullable|numeric',
            'amount' => 'nullable|numeric',
        ]);

        $leg->update($validated);

        return $leg->load('client');
    }

    public function findByTripNumber(Request $request)
    {
        $validated = $request->validate([
            'trip_number' => 'required|string',
        ]);

        $trip = Trip::with(['truck', 'trailer', 'driver', 'convoy', 'legs.client'])
            ->where('trip_number', $validated['trip_number'])
            ->first();

        if (!$trip) {
            return response()->json(['message' => 'Trip not found'], 404);
        }

        return $trip;
    }
}