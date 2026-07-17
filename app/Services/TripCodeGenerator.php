<?php

namespace App\Services;

use App\Models\Trip;

class TripCodeGenerator
{
    public static function generate(string $truckRegNo): string
    {
        $serial = Trip::max('id') + 1;
        $paddedSerial = str_pad($serial, 3, '0', STR_PAD_LEFT);
        $cleanRegNo = strtoupper(str_replace(' ', '', $truckRegNo));

        return "T{$paddedSerial}{$cleanRegNo}";
    }
}