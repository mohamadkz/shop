<?php

namespace App\Models;

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
}
