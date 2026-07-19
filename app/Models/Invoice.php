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
        'created_by',
    ];

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