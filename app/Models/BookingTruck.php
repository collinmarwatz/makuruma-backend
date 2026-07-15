<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class BookingTruck extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_leg_id',
        'truck_id',
        'trailer_id',
        'driver_id',
        'capacity_override',
        'cargo',
        'invoiced_transit_weight',
        'invoiced_detention_charge',
        'rate',
        'quantity',
        'amount',
        'actual_loading_date',
        'actual_offloading_date',
    ];

    protected $appends = ['is_overdue', 'truck_trip_code'];

    protected static function booted(): void
    {
        static::saving(function (BookingTruck $bookingTruck) {
            if ($bookingTruck->rate !== null && $bookingTruck->quantity !== null) {
                $bookingTruck->amount = round($bookingTruck->rate * $bookingTruck->quantity, 2);
            }
        });
    }

    public function tripLeg(): BelongsTo
    {
        return $this->belongsTo(TripLeg::class);
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

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function getIsOverdueAttribute(): bool
    {
        if (!$this->tripLeg?->eta) {
            return false;
        }

        $truck = $this->truck;
        $isFinished = $truck?->current_status === 'completed';

        return !$isFinished && now()->startOfDay()->greaterThan($this->tripLeg->eta);
    }

    public function getTruckTripCodeAttribute(): string
    {
        $tripNumber = $this->tripLeg?->trip?->trip_number ?? '';
        $regNo = $this->truck?->reg_no ?? '';

        return trim("{$tripNumber} {$regNo}");
    }
}