<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trailer;
use Illuminate\Http\Request;

class TrailerDocumentController extends Controller
{
    public function index(Trailer $trailer)
    {
        return $trailer->documents;
    }

    public function store(Request $request, Trailer $trailer)
    {
        $validated = $request->validate([
            'document_type' => 'required|string',
            'expiry_date' => 'nullable|date',
            'attachment' => 'nullable|file|max:5120',
        ]);

        if ($request->hasFile('attachment')) {
            $validated['attachment_path'] = $request->file('attachment')->store('documents', 'public');
        }

        $document = $trailer->documents()->create($validated);

        return response()->json($document, 201);
    }
}