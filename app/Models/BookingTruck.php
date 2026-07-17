<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class BookingTruck extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'trip_id',
        'truck_id',
        'trailer_id',
        'driver_id',
        'capacity',
        'cargo',
        'rate',
        'loading_point_arrival_date',
        'loading_date',
        'loading_dispatch_date',
        'offloading_point_arrival_date',
        'offloading_date',
    ];

    protected $appends = ['is_overdue'];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
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

    public function invoiceLines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class);
    }

    public function getIsOverdueAttribute(): bool
    {
        if (!$this->booking?->eta) {
            return false;
        }

        $isFinished = $this->truck?->current_status === 'completed';

        return !$isFinished && now()->startOfDay()->greaterThan($this->booking->eta);
    }
}