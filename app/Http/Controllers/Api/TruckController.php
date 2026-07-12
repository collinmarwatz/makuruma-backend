<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Truck;
use Illuminate\Http\Request;

class TruckController extends Controller
{
    public function index()
{
    return Truck::with('documents')->get();
}

public function show(Truck $truck)
{
    return $truck->load('documents');
}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reg_no' => 'required|string|unique:trucks,reg_no',
            'capacity' => 'required|numeric',
            'status' => 'in:active,maintenance,decommissioned',
        ]);

        $truck = Truck::create($validated);

        return response()->json($truck, 201);
    }



    public function update(Request $request, Truck $truck)
    {
        $validated = $request->validate([
            'reg_no' => 'sometimes|required|string|unique:trucks,reg_no,' . $truck->id,
            'capacity' => 'sometimes|required|numeric',
            'status' => 'in:active,maintenance,decommissioned',
        ]);

        $truck->update($validated);

        return $truck;
    }

    public function destroy(Truck $truck)
    {
        $truck->delete();

        return response()->json(null, 204);
    }
}