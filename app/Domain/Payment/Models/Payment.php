<?php

namespace App\Domain\Payment\Models;

use App\Domain\Customer\Models\User;
use App\Domain\Order\Models\Order;
use Database\Factories\PaymentFactory;
use App\Shared\Traits\HasUuid;
use App\Domain\Payment\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;


class Payment extends Model
{
    use HasFactory, SoftDeletes, HasUuid;

    protected $fillable = [
        'order_id',
        'user_id',
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

    protected $hidden = [
        'card_pan', 
    ];

    public function scopeSuccess($query)
    {
        return $query->where('status',PaymentStatus::Success);
    }

    public function scopePending($query)
    {
        return $query->where('status', PaymentStatus::Pending);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function newFactory(): PaymentFactory
    {
        return PaymentFactory::new();
    }
}
