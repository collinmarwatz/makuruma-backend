<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Checkpoint;
use Illuminate\Http\Request;

class CheckpointController extends Controller
{
    public function index()
    {
        return Checkpoint::orderBy('sequence_order')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:checkpoints,name',
            'sequence_order' => 'nullable|integer',
        ]);

        return response()->json(Checkpoint::create($validated), 201);
    }
}