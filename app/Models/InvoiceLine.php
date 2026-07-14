<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceLine extends Model
{
    use HasFactory;

    protected $fillable = ['invoice_id', 'booking_truck_id', 'description', 'quantity', 'rate', 'amount'];

public function bookingTruck(): BelongsTo
{
    return $this->belongsTo(BookingTruck::class);
}

    public function tripLeg(): BelongsTo
    {
        return $this->belongsTo(TripLeg::class);
    }
}