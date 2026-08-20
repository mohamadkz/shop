<?php

namespace App\Domain\Cart\Models;

use Database\Factories\DiscountCodeFactory;
use App\Domain\Cart\Enums\DiscountType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscountCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'percent',
        'fixed_amount',
        'max_discount',
        'expired_at',
        'usage_limit',
        'used_count',
    ];

    protected $casts = [
        'type'         => DiscountType::class,
        'percent'      => 'decimal:2',
        'fixed_amount' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'expired_at'   => 'datetime',
        'usage_limit'  => 'integer',
        'used_count'   => 'integer',
    ];

    public function scopeValid($query)
    {
        return $query->where(fn($q) => $q->whereNull('expired_at')->orWhere('expired_at', '>', now()))
            ->where(fn($q) => $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit'));
    }

    public function calculateDiscount(float $amount): float
    {
        $discount = $this->type === DiscountType::Fixed
            ? (float) $this->fixed_amount
            : $amount * ((float) $this->percent / 100);

        if ($this->max_discount) {
            $discount = min($discount, (float) $this->max_discount);
        }

        return round(min($discount, $amount), 2);
    }

    public function baskets()
    {
        return $this->hasMany(Basket::class);
    }

    protected static function newFactory(): DiscountCodeFactory
    {
        return DiscountCodeFactory::new();
    }
}
