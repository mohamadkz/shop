<?php

namespace App\Domain\Cart\Models;

use App\Domain\Catalog\Models\Item;
use Database\Factories\BasketItemFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;


class BasketItem extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'basket_id',
        'item_id',
        'quantity',
        'price',
    ];

    public function basket()
    {
        return $this->belongsTo(Basket::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    protected static function newFactory(): BasketItemFactory
    {
        return BasketItemFactory::new();
    }
}
