<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionSettlement extends Model
{
    protected $fillable = [
        'driver_id',
        'integrator_order_id',
        'amount',
        'currency_code',
        'status',
        'checkout_url',
        'gateway_payload',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_payload' => 'array',
        'paid_at' => 'datetime',
    ];

    public function driver()
    {
        return $this->belongsTo(AppUser::class, 'driver_id');
    }
}
