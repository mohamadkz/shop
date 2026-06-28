<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Order extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'basket_id',
        'total_price',
        'address',
        'status',
    ];

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
        return $this->hasOne(Payment::class);
    }

    public function followups()
    {
        return $this->hasMany(Followup::class);
    }
}
