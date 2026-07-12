<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = ['full_name', 'phone'];

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}