<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Convoy;
use Illuminate\Http\Request;

class ConvoyController extends Controller
{
    public function index()
    {
        return Convoy::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['name' => 'nullable|string']);

        return response()->json(Convoy::create($validated), 201);
    }
}