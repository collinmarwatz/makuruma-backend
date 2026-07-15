<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_order_id',
        'line_category',
        'vendor_id',
        'booking_truck_id',
        'group_key',
        'description',
        'currency',
        'exchange_rate',
        'original_amount',
        'amount',
    ];

    public function bookingTruck(): BelongsTo
    {
        return $this->belongsTo(BookingTruck::class);
    }
    protected static function booted(): void
    {
        static::saving(function (ExpenseLine $line) {
            if ($line->original_amount !== null) {
                $rate = $line->currency === 'TZS' ? 1 : ($line->exchange_rate ?: 1);
                $line->amount = round($line->original_amount * $rate, 2);
            }
        });
    }

    public function expenseOrder(): BelongsTo
    {
        return $this->belongsTo(ExpenseOrder::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}