<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\TripLeg;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TripLegController extends Controller
{
    public function store(Request $request, Trip $trip)
    {
        $controller = new TripController();
        $rules = $controller->truckAndLegRules();
        $rules['direction'] = 'required|in:go,return';

        $validated = $request->validate($rules);

        $leg = DB::transaction(function () use ($trip, $validated, $controller) {
            $leg = $trip->legs()->create([
                'direction' => $validated['direction'],
                'client_id' => $validated['client_id'] ?? null,
                'rate' => $validated['rate'] ?? null,
                'eta' => $validated['eta'] ?? null,
                'location' => $validated['location'] ?? null,
                'item_sn' => $validated['item_sn'] ?? null,
                'description' => $validated['description'] ?? null,
                'quantity' => $validated['quantity'] ?? null,
                'amount' => $validated['amount'] ?? null,
            ]);

            $controller->attachTrucks($leg, $validated['trucks']);

            return $leg;
        });

        return response()->json($leg->load(['client', 'bookingTrucks.truck', 'bookingTrucks.trailer', 'bookingTrucks.driver']), 201);
    }

    public function update(Request $request, TripLeg $leg)
    {
        $controller = new TripController();
        $validated = $request->validate($controller->truckAndLegRules());

        DB::transaction(function () use ($leg, $validated, $controller) {
            $leg->update([
                'client_id' => $validated['client_id'] ?? null,
                'rate' => $validated['rate'] ?? null,
                'eta' => $validated['eta'] ?? null,
                'location' => $validated['location'] ?? null,
                'item_sn' => $validated['item_sn'] ?? null,
                'description' => $validated['description'] ?? null,
                'quantity' => $validated['quantity'] ?? null,
                'amount' => $validated['amount'] ?? null,
            ]);

            $leg->bookingTrucks()->delete();
            $controller->attachTrucks($leg, $validated['trucks']);
        });

        return $leg->load(['client', 'bookingTrucks.truck', 'bookingTrucks.trailer', 'bookingTrucks.driver']);
    }

    public function findByTripNumber(Request $request)
    {
        $validated = $request->validate(['trip_number' => 'required|string']);

        $trip = Trip::with(['legs.client', 'legs.bookingTrucks.truck', 'legs.bookingTrucks.trailer', 'legs.bookingTrucks.driver'])
            ->where('trip_number', $validated['trip_number'])
            ->first();

        if (!$trip) {
            return response()->json(['message' => 'Trip not found'], 404);
        }

        return $trip;
    }
}