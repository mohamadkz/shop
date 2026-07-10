<?php

namespace App\Models;

use App\Enums\OrderStatus;
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

    protected $casts = [
        'status' => OrderStatus::class,
        'total_price' => 'decimal:2',
    ];

    protected function statusLabel(): Attribute
    {
        return Attribute::make(

            get: function () {

                return match ($this->status) {
                    OrderStatus::Pending => "در انتظار پرداخت",
                    OrderStatus::Paid => "پرداخت شده",
                    OrderStatus::Processing => "در حال پردازش",
                    OrderStatus::Completed => "تکمیل شده",
                    OrderStatus::Cancelled => "لغو شده",
                    OrderStatus::Failed => "ناموفق",
                    OrderStatus::Shipped => "ارسال شده",
                };
            }

        );
    }

    public function scopePaid($query)
    {
        return $query->where('status', OrderStatus::Paid);
    }

    public function scopePending($query)
    {
        return $query->where('status',OrderStatus::Pending);
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
}
