<?php

namespace App\Domain\Cart\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountCode extends Model
{
    public function baskets()
    {
        return $this->hasMany(Basket::class);
    }
}
