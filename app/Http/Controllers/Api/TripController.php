<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\Truck;
use App\Services\TripNumberGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class TripController extends Controller
{
    protected function eagerLoad()
    {
        return ['legs.client', 'legs.bookingTrucks.truck', 'legs.bookingTrucks.trailer', 'legs.bookingTrucks.driver'];
    }

    public function index()
    {
        return Trip::with($this->eagerLoad())->latest()->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->truckAndLegRules());

        $firstTruck = Truck::findOrFail($validated['trucks'][0]['truck_id']);
        $bookingNumber = TripNumberGenerator::generate($firstTruck->reg_no);

        $trip = DB::transaction(function () use ($validated, $bookingNumber) {
            $trip = Trip::create(['trip_number' => $bookingNumber]);

            $leg = $trip->legs()->create([
                'direction' => 'go',
                'client_id' => $validated['client_id'] ?? null,
                'rate' => $validated['rate'] ?? null,
                'eta' => $validated['eta'] ?? null,
                'location' => $validated['location'] ?? null,
                'item_sn' => $validated['item_sn'] ?? null,
                'description' => $validated['description'] ?? null,
                'quantity' => $validated['quantity'] ?? null,
                'amount' => $validated['amount'] ?? null,
            ]);

            $this->attachTrucks($leg, $validated['trucks']);

            return $trip;
        });

        return response()->json($trip->load($this->eagerLoad()), 201);
    }

    public function show(Trip $trip)
    {
        return $trip->load($this->eagerLoad());
    }

    public function destroy(Trip $trip)
    {
        $trip->delete();

        return response()->json(null, 204);
    }

    public function download(Trip $trip)
{
    $trip->load($this->eagerLoad());

    $logoPath = public_path('images/logo.png');
    $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));

    $pdf = Pdf::loadView('bookings.order-pdf', [
        'trip' => $trip,
        'logoBase64' => $logoBase64,
    ]);

    return $pdf->download("booking-{$trip->trip_number}.pdf");
}

    public function truckAndLegRules(): array
    {
        return [
            'trucks' => 'required|array|min:1',
            'trucks.*.truck_id' => 'required|exists:trucks,id',
            'trucks.*.trailer_id' => 'nullable|exists:trailers,id',
            'trucks.*.driver_id' => 'nullable|exists:drivers,id',
            'trucks.*.capacity_override' => 'nullable|numeric',
            'trucks.*.cargo' => 'nullable|string',
            'trucks.*.loading_point' => 'nullable|string',
            'trucks.*.loading_point_arrival_date' => 'nullable|date',
            'trucks.*.offloading_point' => 'nullable|string',
            'trucks.*.offloading_date' => 'nullable|date',
            'trucks.*.invoiced_transit_weight' => 'nullable|numeric',
            'trucks.*.invoiced_detention_charge' => 'nullable|numeric',

            'client_id' => 'nullable|exists:clients,id',
            'rate' => 'nullable|numeric',
            'eta' => 'nullable|date',
            'location' => 'nullable|string',
            'item_sn' => 'nullable|string',
            'description' => 'nullable|string',
            'quantity' => 'nullable|numeric',
            'amount' => 'nullable|numeric',
        ];
    }

    public function attachTrucks($leg, array $trucks): void
    {
        foreach ($trucks as $truckData) {
            $truck = Truck::find($truckData['truck_id']);

            $leg->bookingTrucks()->create([
                'truck_id' => $truck->id,
                'trailer_id' => $truckData['trailer_id'] ?? $truck->trailer_id,
                'driver_id' => $truckData['driver_id'] ?? $truck->driver_id,
                'capacity_override' => $truckData['capacity_override'] ?? null,
                'cargo' => $truckData['cargo'] ?? null,
                'loading_point' => $truckData['loading_point'] ?? null,
                'loading_point_arrival_date' => $truckData['loading_point_arrival_date'] ?? null,
                'offloading_point' => $truckData['offloading_point'] ?? null,
                'offloading_date' => $truckData['offloading_date'] ?? null,
                'invoiced_transit_weight' => $truckData['invoiced_transit_weight'] ?? null,
                'invoiced_detention_charge' => $truckData['invoiced_detention_charge'] ?? null,
            ]);
        }
    }
}