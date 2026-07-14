<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookingTruck;
use App\Models\Invoice;
use App\Services\InvoiceNumberGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    protected function eagerLoad()
    {
        return ['lines.bookingTruck.truck', 'lines.bookingTruck.tripLeg.trip', 'client', 'creator'];
    }

    public function index()
    {
        return Invoice::with($this->eagerLoad())->latest()->get();
    }

    public function invoiceableLegs(Request $request)
    {
        $validated = $request->validate(['client_id' => 'required|exists:clients,id']);

        return BookingTruck::with(['truck', 'tripLeg.trip'])
            ->whereHas('tripLeg', fn ($q) => $q->where('client_id', $validated['client_id']))
            ->whereDoesntHave('invoiceLine')
            ->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'invoice_date' => 'required|date',
            'mode_of_payment' => 'nullable|string',
            'booking_truck_ids' => 'required|array|min:1',
            'booking_truck_ids.*' => 'exists:booking_trucks,id',
        ]);

        $invoiceNumber = InvoiceNumberGenerator::generate();

        $invoice = DB::transaction(function () use ($validated, $invoiceNumber, $request) {
            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'client_id' => $validated['client_id'],
                'invoice_date' => $validated['invoice_date'],
                'mode_of_payment' => $validated['mode_of_payment'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            $bookingTrucks = BookingTruck::with('truck', 'tripLeg')->whereIn('id', $validated['booking_truck_ids'])->get();

            foreach ($bookingTrucks as $bt) {
                $invoice->lines()->create([
                    'booking_truck_id' => $bt->id,
                    'description' => $bt->cargo ?? $bt->tripLeg->description ?? 'Freight service — ' . $bt->truck->reg_no,
                    'quantity' => $bt->quantity,
                    'rate' => $bt->rate,
                    'amount' => $bt->amount,
                ]);
            }

            $invoice->recalculateTotal();

            return $invoice;
        });

        return response()->json($invoice->load($this->eagerLoad()), 201);
    }

    public function show(Invoice $invoice)
    {
        return $invoice->load($this->eagerLoad());
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        return response()->json(null, 204);
    }

    public function download(Invoice $invoice)
    {
        $invoice->load($this->eagerLoad());

        $logoPath = public_path('images/logo.png');
        $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));

        $pdf = Pdf::loadView('invoices.invoice-pdf', [
            'invoice' => $invoice,
            'logoBase64' => $logoBase64,
        ]);

        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }
}