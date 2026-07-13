<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookingTruck;
use App\Models\TruckMilestone;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;


class TrackingController extends Controller
{
    public function index()
    {
        return BookingTruck::with([
            'truck', 'trailer', 'driver',
            'tripLeg.trip',
            'milestones.checkpoint',
        ])->latest()->get();
    }

    public function download(BookingTruck $bookingTruck)
{
    $bookingTruck->load(['truck', 'trailer', 'driver', 'tripLeg.trip', 'milestones.checkpoint']);

    $sortedMilestones = $bookingTruck->milestones->sortBy('checkpoint.sequence_order');

    $logoPath = public_path('images/logo.png');
    $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));

    $pdf = Pdf::loadView('tracking.report-pdf', [
        'bookingTruck' => $bookingTruck,
        'milestones' => $sortedMilestones,
        'logoBase64' => $logoBase64,
    ]);

    $filename = "tracking-{$bookingTruck->truck->reg_no}-{$bookingTruck->tripLeg->trip->trip_number}.pdf";

    return $pdf->download($filename);
}

    public function show(BookingTruck $bookingTruck)
    {
        return $bookingTruck->load(['truck', 'trailer', 'driver', 'tripLeg.trip', 'milestones.checkpoint']);
    }

    public function updateStatus(Request $request, BookingTruck $bookingTruck)
    {
        $validated = $request->validate([
            'current_location' => 'nullable|string',
            'current_status' => 'required|in:loading,in_transit,at_border,offloading,delayed,completed',
        ]);

        $bookingTruck->update($validated);

        return $bookingTruck->load(['truck', 'trailer', 'driver', 'tripLeg.trip', 'milestones.checkpoint']);
    }

    public function upsertMilestone(Request $request, BookingTruck $bookingTruck)
    {
        $validated = $request->validate([
            'checkpoint_id' => 'required|exists:checkpoints,id',
            'arrival_at' => 'nullable|date',
            'dispatch_at' => 'nullable|date',
        ]);

        $milestone = TruckMilestone::updateOrCreate(
            [
                'booking_truck_id' => $bookingTruck->id,
                'checkpoint_id' => $validated['checkpoint_id'],
            ],
            [
                'arrival_at' => $validated['arrival_at'] ?? null,
                'dispatch_at' => $validated['dispatch_at'] ?? null,
            ]
        );

        return response()->json($milestone->load('checkpoint'), 200);
    }
}