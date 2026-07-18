<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ExpenseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'category',
        'booking_id',
        'truck_id',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'paid_by',
        'paid_at',
        'total_amount',
        'payment_account',
        'initiated_by',
        'payment_date',
    ];
    protected $casts = [
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];



    public function lines(): HasMany
    {
        return $this->hasMany(ExpenseLine::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function truck(): BelongsTo
    {
        return $this->belongsTo(Truck::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }


    public function trucks(): BelongsToMany
    {
        return $this->belongsToMany(Truck::class, 'expense_order_trucks');
    }

    public function recalculateTotal(): void
    {
        $this->update(['total_amount' => $this->lines()->sum('amount')]);
    }
}