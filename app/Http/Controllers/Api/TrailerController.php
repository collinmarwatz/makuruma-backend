<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trailer;
use Illuminate\Http\Request;

class TrailerController extends Controller
{
    public function index()
    {
        return Trailer::with('documents')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reg_no' => 'required|string|unique:trailers,reg_no',
        ]);

        $trailer = Trailer::create($validated);

        return response()->json($trailer->load('documents'), 201);
    }

    public function show(Trailer $trailer)
    {
        return $trailer->load('documents');
    }

    public function update(Request $request, Trailer $trailer)
    {
        $validated = $request->validate([
            'reg_no' => 'sometimes|required|string|unique:trailers,reg_no,' . $trailer->id,
        ]);

        $trailer->update($validated);

        return $trailer->load('documents');
    }

    public function destroy(Trailer $trailer)
    {
        $trailer->delete();

        return response()->json(null, 204);
    }
}