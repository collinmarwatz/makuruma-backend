<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingTruck;
use App\Models\Client;
use App\Models\Trip;
use App\Models\Truck;
use App\Services\BookingNumberGenerator;
use App\Services\TripCodeGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    protected function eagerLoad()
    {
        return [
            'client',
            'creator',
            'bookingTrucks.truck',
            'bookingTrucks.trailer',
            'bookingTrucks.driver.documents',
            'bookingTrucks.documents',
            'bookingTrucks.trip',
        ];
    }
    public function index()
    {
        return Booking::with($this->eagerLoad())->latest()->get();
    }

    public function show(Booking $booking)
    {
        return $booking->load($this->eagerLoad());
    }

    /**
     * Trucks eligible for a NEW booking, given the requested direction.
     * - Go: only trucks currently off_duty
     * - Return: only trucks currently on an open 'go' trip
     */
    public function eligibleTrucks(Request $request)
    {
        $validated = $request->validate(['direction' => 'required|in:go,return']);

        $status = $validated['direction'] === 'go' ? 'off_duty' : 'go';

        return Truck::with(['trailer', 'driver.documents'])
            ->where('trip_status', $status)
            ->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'direction' => 'required|in:go,return',
            'client_id' => 'required|exists:clients,id',
            'eta' => 'nullable|date',
            'location' => 'nullable|string',
            'loading_point' => 'nullable|string',
            'offloading_point' => 'nullable|string',
            'description' => 'nullable|string',
            'truck_ids' => 'required|array|min:1',
            'truck_ids.*' => 'exists:trucks,id',
        ]);

        $client = Client::findOrFail($validated['client_id']);
        $bookingNumber = BookingNumberGenerator::generate($client, count($validated['truck_ids']));

        $booking = DB::transaction(function () use ($validated, $bookingNumber, $request) {
            $booking = Booking::create([
                'booking_number' => $bookingNumber,
                'direction' => $validated['direction'],
                'client_id' => $validated['client_id'],
                'eta' => $validated['eta'] ?? null,
                'location' => $validated['location'] ?? null,
                'loading_point' => $validated['loading_point'] ?? null,
                'offloading_point' => $validated['offloading_point'] ?? null,
                'description' => $validated['description'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            foreach ($validated['truck_ids'] as $truckId) {
                $truck = Truck::findOrFail($truckId);

                $bookingTruck = $booking->bookingTrucks()->create([
                    'truck_id' => $truck->id,
                    'trailer_id' => $truck->trailer_id,
                    'driver_id' => $truck->driver_id,
                    'capacity' => $truck->capacity,
                ]);

                if ($validated['direction'] === 'go') {
                    // Fresh trip for this truck's journey
                    $trip = Trip::create([
                        'trip_code' => TripCodeGenerator::generate($truck->reg_no),
                        'truck_id' => $truck->id,
                        'go_booking_truck_id' => $bookingTruck->id,
                    ]);

                    $bookingTruck->update(['trip_id' => $trip->id]);
                    $truck->update(['trip_status' => 'go']);
                } else {
                    // Find this truck's currently open trip and attach the return leg
                    $openTrip = Trip::where('truck_id', $truck->id)
                        ->whereNull('return_booking_truck_id')
                        ->latest()
                        ->first();

                    if ($openTrip) {
                        $openTrip->update(['return_booking_truck_id' => $bookingTruck->id]);
                        $bookingTruck->update(['trip_id' => $openTrip->id]);
                    }

                    $truck->update(['trip_status' => 'return']);
                }
            }

            return $booking;
        });

        return response()->json($booking->load($this->eagerLoad()), 201);
    }

    public function update(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'eta' => 'nullable|date',
            'location' => 'nullable|string',
            'loading_point' => 'nullable|string',
            'offloading_point' => 'nullable|string',
            'description' => 'nullable|string',
            'trucks' => 'required|array|min:1',
            'trucks.*.booking_truck_id' => 'required|exists:booking_trucks,id',
            'trucks.*.cargo' => 'nullable|string',
            'trucks.*.rate' => 'nullable|numeric',
            'trucks.*.trailer_id' => 'nullable|exists:trailers,id',
            'trucks.*.driver_id' => 'nullable|exists:drivers,id',
        ]);

        DB::transaction(function () use ($booking, $validated) {
            $booking->update([
                'eta' => $validated['eta'] ?? null,
                'location' => $validated['location'] ?? null,
                'loading_point' => $validated['loading_point'] ?? null,
                'offloading_point' => $validated['offloading_point'] ?? null,
                'description' => $validated['description'] ?? null,
            ]);

            foreach ($validated['trucks'] as $truckData) {
                $bookingTruck = BookingTruck::find($truckData['booking_truck_id']);
                if (!$bookingTruck || $bookingTruck->booking_id !== $booking->id)
                    continue;

                $bookingTruck->update([
                    'cargo' => $truckData['cargo'] ?? null,
                    'rate' => $truckData['rate'] ?? null,
                    'trailer_id' => $truckData['trailer_id'] ?? $bookingTruck->trailer_id,
                    'driver_id' => $truckData['driver_id'] ?? $bookingTruck->driver_id,
                ]);
            }
        });

        return $booking->load($this->eagerLoad());
    }

    public function destroy(Booking $booking)
    {
        $booking->delete();

        return response()->json(null, 204);
    }
}