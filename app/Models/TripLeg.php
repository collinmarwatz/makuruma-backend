<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TripLeg extends Model
{
    use HasFactory;

    protected $fillable = [
    'trip_id', 'direction', 'client_id', 'eta',
    'location', 'item_sn', 'description',
];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function bookingTrucks(): HasMany
    {
        return $this->hasMany(BookingTruck::class);
    }

    public function invoiceLines()
{
    return $this->hasMany(InvoiceLine::class);
}
}