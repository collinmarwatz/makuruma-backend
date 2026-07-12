<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Staff extends Model
{
    use HasFactory;

    protected $table = 'staff';

    protected $fillable = ['full_name', 'phone', 'tin_number', 'payment_account'];

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}