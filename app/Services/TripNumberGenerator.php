<?php

namespace App\Services;

use App\Models\Trip;

class TripNumberGenerator
{
    public static function generate(): string
    {
        $serial = Trip::max('id') + 1;

        return 'Trip ' . $serial;
    }
}