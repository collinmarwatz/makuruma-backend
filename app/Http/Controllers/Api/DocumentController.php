<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function show(Document $document)
    {
        return $document;
    }

    public function update(Request $request, Document $document)
    {
        $validated = $request->validate([
            'document_type' => 'sometimes|required|string',
            'number' => 'nullable|string',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
        ]);

        $document->update($validated);

        return $document;
    }

    public function destroy(Document $document)
    {
        $document->delete();

        return response()->json(null, 204);
    }
}