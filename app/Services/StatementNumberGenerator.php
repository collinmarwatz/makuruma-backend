<?php

namespace App\Services;

class StatementNumberGenerator
{
    public static function generate(): string
    {
        $serial = random_int(100, 999) + (int) now()->format('Hi');
        return 'S' . str_pad($serial, 5, '0', STR_PAD_LEFT);
    }
}