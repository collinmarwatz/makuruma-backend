<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'deal_no',
        'bivac_no',
        'purchase_order_no',
        'invoice_type',
        'booking_id',
        'client_id',
        'invoice_date',
        'mode_of_payment',
        'delivery_note_no',
        'delivery_note_date',
        'supplier_ref',
        'other_ref',
        'loading_con_no',
        'settlement_no',
        'dispatched_through',
        'destination',
        'terms_of_delivery',
        'proof_of_delivery_path',
        'total_amount',
        'exchange_rate',
        'status',
        'paid_at',
        'paid_by',
        'created_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'paid_at' => 'datetime',
    ];

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    protected $appends = ['total_amount_tzs'];

    public function getTotalAmountTzsAttribute(): float
    {
        $rate = $this->exchange_rate ?: 1;
        return round(((float) $this->total_amount) * $rate, 2);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class);
    }

    public function recalculateTotal(): void
    {
        $this->update(['total_amount' => $this->lines()->sum('amount')]);
    }
}