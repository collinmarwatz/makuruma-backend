<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;

class TripController extends Controller
{
    protected function eagerLoad()
    {
        return [
            'truck',
            'goBookingTruck.booking.client',
            'goBookingTruck.trailer',
            'goBookingTruck.driver',
            'returnBookingTruck.booking.client',
            'returnBookingTruck.trailer',
            'returnBookingTruck.driver',
        ];
    }

    public function index()
    {
        return Trip::with($this->eagerLoad())->latest()->get();
    }

    public function show(Trip $trip)
    {
        return $trip->load($this->eagerLoad());
    }
}