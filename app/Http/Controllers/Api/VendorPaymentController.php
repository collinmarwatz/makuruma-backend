<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VendorPayment;
use Illuminate\Http\Request;

class VendorPaymentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $payment = VendorPayment::create([
            ...$validated,
            'created_by' => $request->user()->id,
        ]);

        return response()->json($payment->load('creator'), 201);
    }

    public function destroy(VendorPayment $vendorPayment)
    {
        $vendorPayment->delete();

        return response()->json(null, 204);
    }
}