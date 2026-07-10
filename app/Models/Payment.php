<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'authority',
        'amount',
        'payment_method',
        'transaction_id',
        'ref_id',
        'tracking_code',
        'card_pan',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'status' => PaymentStatus::class,
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function scopeSuccess($query)
    {
        return $query->where('status',PaymentStatus::Success);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
