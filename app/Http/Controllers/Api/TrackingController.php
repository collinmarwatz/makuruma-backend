<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Truck;
use App\Models\TruckMilestone;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class TrackingController extends Controller
{
    protected function eagerLoad()
    {
        return ['trailer', 'driver', 'milestones.checkpoint', 'bookingTrucks' => function ($query) {
            $query->latest()->limit(1)->with('tripLeg.trip');
        }];
    }

    public function index()
    {
        return Truck::with($this->eagerLoad())->get();
    }

    public function show(Truck $truck)
    {
        return $truck->load($this->eagerLoad());
    }

    public function updateStatus(Request $request, Truck $truck)
    {
        $validated = $request->validate([
            'current_location' => 'nullable|string',
            'current_status' => 'required|in:loading,in_transit,at_border,offloading,delayed,completed',
        ]);

        $truck->update($validated);

        return $truck->load($this->eagerLoad());
    }

    public function upsertMilestone(Request $request, Truck $truck)
    {
        $validated = $request->validate([
            'checkpoint_id' => 'required|exists:checkpoints,id',
            'arrival_at' => 'nullable|date',
            'dispatch_at' => 'nullable|date',
        ]);

        $milestone = TruckMilestone::updateOrCreate(
            [
                'truck_id' => $truck->id,
                'checkpoint_id' => $validated['checkpoint_id'],
            ],
            [
                'arrival_at' => $validated['arrival_at'] ?? null,
                'dispatch_at' => $validated['dispatch_at'] ?? null,
            ]
        );

        return response()->json($milestone->load('checkpoint'), 200);
    }

    public function download(Truck $truck)
    {
        $truck->load($this->eagerLoad());

        $sortedMilestones = $truck->milestones->sortBy('checkpoint.sequence_order');

        $logoPath = public_path('images/logo.png');
        $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));

        $pdf = Pdf::loadView('tracking.report-pdf', [
            'truck' => $truck,
            'milestones' => $sortedMilestones,
            'logoBase64' => $logoBase64,
        ]);

        return $pdf->download("tracking-{$truck->reg_no}.pdf");
    }
}