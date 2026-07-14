<?php

namespace App\Services;

use App\Models\Invoice;

class InvoiceNumberGenerator
{
    public static function generate(): string
    {
        $serial = Invoice::max('id') + 1;

        return 'INV-' . str_pad($serial, 5, '0', STR_PAD_LEFT);
    }
}