<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'user_id', 'attraction_id', 'name', 'email', 'phone', 'date', 'quantity', 'total', 'payment_method'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attraction()
    {
        return $this->belongsTo(Attraction::class);
    }
}
