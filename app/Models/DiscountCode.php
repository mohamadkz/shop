<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountCode extends Model
{
    public function baskets()
    {
        return $this->hasMany(Basket::class);
    }
}
