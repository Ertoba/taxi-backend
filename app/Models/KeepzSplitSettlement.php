<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KeepzSplitSettlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'transaction_id',
        'driver_id',
        'integrator_order_id',
        'currency_code',
        'total_amount',
        'platform_amount',
        'driver_amount',
        'platform_receiver_type',
        'platform_receiver_masked',
        'driver_receiver_type',
        'driver_receiver_masked',
        'gateway_status',
        'gateway_payload',
        'paid_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'platform_amount' => 'decimal:2',
        'driver_amount' => 'decimal:2',
        'gateway_payload' => 'array',
        'paid_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function driver()
    {
        return $this->belongsTo(AppUser::class, 'driver_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
