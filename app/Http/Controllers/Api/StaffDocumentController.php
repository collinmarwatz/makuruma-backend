<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\Http\Request;

class StaffDocumentController extends Controller
{
    public function index(Staff $staff)
    {
        return $staff->documents;
    }

    public function store(Request $request, Staff $staff)
    {
        $validated = $request->validate([
            'document_type' => 'required|string',
            'expiry_date' => 'nullable|date',
            'attachment' => 'nullable|file|max:5120',
        ]);

        if ($request->hasFile('attachment')) {
            $validated['attachment_path'] = $request->file('attachment')->store('documents', 'public');
        }

        $document = $staff->documents()->updateOrCreate(
            ['document_type' => $validated['document_type']],
            $validated
        );

        return response()->json($document, 201);
    }
}