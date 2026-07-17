<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OfficeAsset;
use Illuminate\Http\Request;

class OfficeAssetController extends Controller
{
    public function index()
    {
        return OfficeAsset::latest()->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'category' => 'required|in:furniture,electronics,equipment,vehicle,other',
            'serial_number' => 'nullable|string',
            'buying_price' => 'nullable|numeric',
            'purchase_date' => 'nullable|date',
            'location' => 'nullable|string',
            'condition' => 'required|in:active,under_repair,disposed',
            'notes' => 'nullable|string',
        ]);

        return response()->json(OfficeAsset::create($validated), 201);
    }

    public function show(OfficeAsset $officeAsset)
    {
        return $officeAsset;
    }

    public function update(Request $request, OfficeAsset $officeAsset)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string',
            'category' => 'sometimes|required|in:furniture,electronics,equipment,vehicle,other',
            'serial_number' => 'nullable|string',
            'buying_price' => 'nullable|numeric',
            'purchase_date' => 'nullable|date',
            'location' => 'nullable|string',
            'condition' => 'sometimes|required|in:active,under_repair,disposed',
            'notes' => 'nullable|string',
        ]);

        $officeAsset->update($validated);

        return $officeAsset;
    }

    public function destroy(OfficeAsset $officeAsset)
    {
        $officeAsset->delete();

        return response()->json(null, 204);
    }
}