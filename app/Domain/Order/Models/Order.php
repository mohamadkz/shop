<?php

namespace App\Domain\Order\Models;

use App\Domain\Customer\Models\User;
use App\Domain\Payment\Models\Payment;
use App\Domain\Cart\Models\Basket;
use App\Domain\Order\Enums\OrderStatus;
use App\Shared\Traits\HasUuid;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;


class Order extends Model
{
    use HasFactory, SoftDeletes, HasUuid;

    protected $fillable = [
        'user_id',
        'order_number',
        'idempotency_key',
        'subtotal',
        'discount_amount',
        'discount_code',
        'tax',
        'basket_id',
        'total_price',
        'address',
        'status',
    ];

    protected $casts = [
        'status'          => OrderStatus::class,
        'subtotal'        => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax'             => 'decimal:2',
        'total_price'     => 'decimal:2',
    ];

    protected function statusLabel(): Attribute
    {
        return Attribute::make(get: fn () => $this->status->label());
    }

    public function scopePaid($query)
    {
        return $query->where('status', OrderStatus::Paid);
    }

    public function scopePending($query)
    {
        return $query->where('status',OrderStatus::Pending);
    }

    public function scopeOwnedBy($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function basket()
    {
        return $this->belongsTo(Basket::class);
    }

    public function payment()
    {
        return $this->hasMany(Payment::class);
    }

    public function followups()
    {
        return $this->hasMany(Followup::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    protected static function newFactory(): OrderFactory
    {
        return OrderFactory::new();
    }

    public function toStockLines(): array
    {
        return $this->items->map(fn (OrderItem $i) => [
            'item_id'  => $i->item_id,
            'quantity' => $i->quantity,
        ])->all();
    }
}
