<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Basket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'total_amount',
        'discount_amount',
        'amount',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function basketItems()
    {
        return $this->hasMany(BasketItem::class);
    }

    public function discountCode()
    {
        return $this->belongsTo(DiscountCode::class);
    }


    public function calculateTotals(): void
    {
        $this->loadMissing(['basketItems', 'discountCode']);

        $this->amount = $this->basketItems->sum(
            fn($item) => $item->price * $item->quantity
        );

        $discountAmount = 0;
        if ($this->discountCode) {
            $discountAmount = ($this->amount * $this->discountCode->percent) / 100;

            if ($this->discountCode->max_discount) {
                $discountAmount = min($discountAmount, $this->discountCode->max_discount);
            }
        }

        $this->discount_amount = $discountAmount;
        $this->total_amount = max(0, $this->amount - $discountAmount);
        $this->saveQuietly();
    }
}
