<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingTruck extends Model
{
    use HasFactory;

protected $fillable = [
    'trip_id', 'truck_id', 'trailer_id', 'driver_id', 'capacity_override',
    'cargo', 'loading_point', 'loading_point_arrival_date',
    'offloading_point', 'offloading_date',
    'invoiced_transit_weight', 'invoiced_detention_charge',
];
    public function tripLeg(): BelongsTo
{
    return $this->belongsTo(TripLeg::class);
}

public function milestones(): HasMany
{
    return $this->hasMany(TruckMilestone::class);
}


    public function truck(): BelongsTo
    {
        return $this->belongsTo(Truck::class);
    }

    public function trailer(): BelongsTo
    {
        return $this->belongsTo(Trailer::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }
}