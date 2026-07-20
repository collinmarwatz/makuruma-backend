<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookingTruck;
use Illuminate\Http\Request;

class BookingTruckDocumentController extends Controller
{
    public function index(BookingTruck $bookingTruck)
    {
        return $bookingTruck->documents;
    }

    public function store(Request $request, BookingTruck $bookingTruck)
    {
        $validated = $request->validate([
            'document_type' => 'required|string',
            'attachment' => 'required|file|max:5120',
        ]);

        $path = $request->file('attachment')->store('documents', 'public');

        $document = $bookingTruck->documents()->updateOrCreate(
            ['document_type' => $validated['document_type']],
            ['attachment_path' => $path]
        );

        return response()->json($document, 201);
    }
}