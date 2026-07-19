<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
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
        return ['booking.client', 'lines.bookingTruck.truck', 'lines.bookingTruck.trailer', 'lines.bookingTruck.trip', 'creator'];
    }

    public function index()
    {
        return Invoice::with($this->eagerLoad())->latest()->get();
    }

    public function show(Invoice $invoice)
    {
        return $invoice->load($this->eagerLoad());
    }

    /**
     * Trucks in this booking that don't yet have an invoice line
     * under this specific invoice type — the "partial invoicing" picker.
     */
    public function eligibleTrucks(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'invoice_type' => 'required|in:advance,settlement,standing_time,adjustment',
        ]);

        $booking = Booking::findOrFail($validated['booking_id']);

        return BookingTruck::where('booking_id', $booking->id)
            ->with(['truck', 'trailer', 'trip'])
            ->whereDoesntHave('invoiceLines', function ($query) use ($validated) {
                $query->whereHas('invoice', function ($q) use ($validated) {
                    $q->where('invoice_type', $validated['invoice_type']);
                });
            })
            ->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_type' => 'required|in:advance,settlement,standing_time,adjustment',
            'booking_id' => 'required|exists:bookings,id',
            'invoice_date' => 'required|date',
            'deal_no' => 'nullable|string',
            'bivac_no' => 'nullable|string',
            'mode_of_payment' => 'nullable|string',
            'delivery_note_no' => 'nullable|string',
            'delivery_note_date' => 'nullable|date',
            'supplier_ref' => 'nullable|string',
            'other_ref' => 'nullable|string',
            'loading_con_no' => 'nullable|string',
            'settlement_no' => 'nullable|string',
            'dispatched_through' => 'nullable|string',
            'destination' => 'nullable|string',
            'terms_of_delivery' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.booking_truck_id' => 'required|exists:booking_trucks,id',
            'lines.*.quantity' => 'required|numeric|min:0',
            'lines.*.rate' => 'required|numeric|min:0',
            'lines.*.days' => 'nullable|integer|min:0',
            'exchange_rate' => 'required|numeric|min:0.0001',
        ]);

        $booking = Booking::with('client')->findOrFail($validated['booking_id']);

        if ($validated['invoice_type'] === 'advance' && !$booking->client->allows_advance_invoice) {
            return response()->json(['message' => 'This client is not eligible for Advance invoices.'], 422);
        }

        $invoice = DB::transaction(function () use ($validated, $booking) {
            $invoice = Invoice::create([
                'invoice_number' => InvoiceNumberGenerator::generate(),
                'invoice_type' => $validated['invoice_type'],
                'booking_id' => $booking->id,
                'client_id' => $booking->client_id,
                'invoice_date' => $validated['invoice_date'],
                'deal_no' => $validated['deal_no'] ?? null,
                'bivac_no' => $validated['bivac_no'] ?? null,
                'mode_of_payment' => $validated['mode_of_payment'] ?? null,
                'delivery_note_no' => $validated['delivery_note_no'] ?? null,
                'delivery_note_date' => $validated['delivery_note_date'] ?? null,
                'supplier_ref' => $validated['supplier_ref'] ?? null,
                'other_ref' => $validated['other_ref'] ?? null,
                'loading_con_no' => $validated['loading_con_no'] ?? null,
                'settlement_no' => $validated['settlement_no'] ?? null,
                'dispatched_through' => $validated['dispatched_through'] ?? null,
                'destination' => $validated['destination'] ?? null,
                'terms_of_delivery' => $validated['terms_of_delivery'] ?? null,
                'created_by' => request()->user()->id,
                'exchange_rate' => $validated['exchange_rate'],
            ]);

            foreach ($validated['lines'] as $lineData) {
                $bookingTruck = BookingTruck::with('truck', 'trailer')->findOrFail($lineData['booking_truck_id']);
                $quantity = $lineData['quantity'];
                $rate = $lineData['rate'];

                $amount = match ($validated['invoice_type']) {
                    'advance' => $quantity * $rate,
                    'settlement' => ($quantity * $rate) - $this->previousAdvanceTotal($bookingTruck->id),
                    'standing_time' => ($lineData['days'] ?? 0) * $rate,
                    'adjustment' => $quantity * $rate,
                };

                $invoice->lines()->create([
                    'booking_truck_id' => $bookingTruck->id,
                    'description' => trim("{$bookingTruck->truck->reg_no}/{$bookingTruck->trailer->reg_no}", '/'),
                    'quantity' => $quantity,
                    'rate' => $rate,
                    'days' => $lineData['days'] ?? null,
                    'amount' => round($amount, 2),
                ]);
            }

            $invoice->recalculateTotal();

            return $invoice;
        });

        return response()->json($invoice->load($this->eagerLoad()), 201);
    }

    protected function previousAdvanceTotal(int $bookingTruckId): float
    {
        return (float) DB::table('invoice_lines')
            ->join('invoices', 'invoices.id', '=', 'invoice_lines.invoice_id')
            ->where('invoice_lines.booking_truck_id', $bookingTruckId)
            ->where('invoices.invoice_type', 'advance')
            ->sum('invoice_lines.amount');
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

        $pdf = Pdf::loadView('invoices.invoice-pdf', ['invoice' => $invoice, 'logoBase64' => $logoBase64]);

        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }
}