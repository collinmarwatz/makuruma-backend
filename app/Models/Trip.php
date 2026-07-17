<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = ['trip_code', 'truck_id', 'go_booking_truck_id', 'return_booking_truck_id'];

    public function truck(): BelongsTo
    {
        return $this->belongsTo(Truck::class);
    }

    public function goBookingTruck(): BelongsTo
    {
        return $this->belongsTo(BookingTruck::class, 'go_booking_truck_id');
    }

    public function returnBookingTruck(): BelongsTo
    {
        return $this->belongsTo(BookingTruck::class, 'return_booking_truck_id');
    }
}