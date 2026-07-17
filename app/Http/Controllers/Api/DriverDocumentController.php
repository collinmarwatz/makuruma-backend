<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\Request;

class DriverDocumentController extends Controller
{
    public function index(Driver $driver)
    {
        return $driver->documents;
    }

    public function store(Request $request, Driver $driver)
    {
        $validated = $request->validate([
            'document_type' => 'required|string',
            'number' => 'nullable|string',
            'expiry_date' => 'nullable|date',
            'attachment' => 'nullable|file|max:5120',
        ]);

        if ($request->hasFile('attachment')) {
            $validated['attachment_path'] = $request->file('attachment')->store('documents', 'public');
        }

        $document = $driver->documents()->updateOrCreate(
            ['document_type' => $validated['document_type']],
            $validated
        );

        return response()->json($document, 201);
    }
}