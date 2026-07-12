<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index()
    {
        return Vendor::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string',
            'vendor_type' => 'required|in:fuel,e-seal',
            'phone' => 'nullable|string',
        ]);

        $vendor = Vendor::create($validated);

        return response()->json($vendor, 201);
    }

    public function show(Vendor $vendor)
    {
        return $vendor;
    }

    public function update(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'company_name' => 'sometimes|required|string',
            'vendor_type' => 'sometimes|required|in:fuel,e-seal',
            'phone' => 'nullable|string',
        ]);

        $vendor->update($validated);

        return $vendor;
    }

    public function destroy(Vendor $vendor)
    {
        $vendor->delete();

        return response()->json(null, 204);
    }
}