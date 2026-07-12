<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Truck;
use Illuminate\Http\Request;

class TruckDocumentController extends Controller
{
    public function index(Truck $truck)
    {
        return $truck->documents;
    }

    public function store(Request $request, Truck $truck)
    {
        $validated = $request->validate([
            'document_type' => 'required|string',
            'number' => 'nullable|string',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'attachment' => 'nullable|file|max:5120',
        ]);

        if ($request->hasFile('attachment')) {
            $validated['attachment_path'] = $request->file('attachment')->store('documents', 'public');
        }

        $document = $truck->documents()->create($validated);

        return response()->json($document, 201);
    }
}