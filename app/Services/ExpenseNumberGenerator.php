<?php

namespace App\Services;

use App\Models\ExpenseOrder;

class ExpenseNumberGenerator
{
    public static function generate(): string
    {
        $serial = ExpenseOrder::max('id') + 1;

        return 'EXP-' . str_pad($serial, 5, '0', STR_PAD_LEFT);
    }
}