<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'user_id', 'attraction_id', 'name', 'email', 'phone', 'date', 'quantity', 'total', 'payment_method', 'status', 'payment_proof',
    ];

    protected $casts = [
        'date' => 'date',
        'total' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attraction()
    {
        return $this->belongsTo(Attraction::class);
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'paid' => 'success',
            'cancelled' => 'danger',
            'refunded' => 'info',
            default => 'secondary',
        };
    }

    public function getPaymentMethodColorAttribute()
    {
        return match($this->payment_method) {
            'visa' => 'primary',
            'mastercard' => 'secondary',
            'paypal' => 'success',
            'bank_transfer' => 'warning',
            'cash' => 'danger',
            default => 'secondary',
        };
    }
}
