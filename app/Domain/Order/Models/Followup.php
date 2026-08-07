<?php

namespace App\Domain\Order\Models;

use Database\Factories\FollowupFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Followup extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'order_id',
        'title',
        'description',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    protected static function newFactory(): FollowupFactory
    {
        return FollowupFactory::new();
    }
}
