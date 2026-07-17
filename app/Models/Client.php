<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = ['company_name', 'short_code', 'allows_advance_invoice', 'email', 'phone'];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
