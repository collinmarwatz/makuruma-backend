<?php

namespace App\Services;

use App\Models\Trip;

class TripNumberGenerator
{
    public static function generate(string $truckRegNo): string
    {
        $serial = Trip::max('id') + 1;

        $cleanRegNo = strtoupper(str_replace(' ', '', $truckRegNo));

        return $serial . $cleanRegNo;
    }
}