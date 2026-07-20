<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceLine extends Model
{
    use HasFactory;

    protected $fillable = ['invoice_id', 'booking_truck_id', 'description', 'quantity', 'rate', 'percentage', 'is_flat_amount', 'days', 'amount'];
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function bookingTruck(): BelongsTo
    {
        return $this->belongsTo(BookingTruck::class);
    }
}