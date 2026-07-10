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

    public function calculateTotals()
    {
        $this->load('basketItems');
        $this->amount = $this->basketItems ? $this->basketItems->sum(function ($item) {
            return $item->price * $item->quantity;
        }) : 0;

        $this->total_amount = max(0, $this->amount - $this->discount_amount);
        $this->save();
    }
}
