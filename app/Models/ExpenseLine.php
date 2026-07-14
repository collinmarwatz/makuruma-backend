<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseLine extends Model
{
    use HasFactory;

    protected $fillable = ['expense_order_id', 'description', 'amount'];

    public function expenseOrder(): BelongsTo
    {
        return $this->belongsTo(ExpenseOrder::class);
    }
}