<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Truck extends Model
{
    use HasFactory;

    protected $fillable = ['reg_no', 'capacity', 'status', 'trailer_id', 'driver_id', 'current_location', 'current_status'];

public function documents()
{
    return $this->morphMany(Document::class, 'documentable');
}

public function trailer()
{
    return $this->belongsTo(Trailer::class);
}

public function driver()
{
    return $this->belongsTo(Driver::class);
}

public function milestones()
{
    return $this->hasMany(TruckMilestone::class);
}

public function bookingTrucks()
{
    return $this->hasMany(BookingTruck::class);
}

         
}