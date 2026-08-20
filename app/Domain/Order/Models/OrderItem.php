<?php

namespace App\Domain\Order\Models;

use Database\Factories\OrderItemFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    // item_id is a plain FK (ID) to Catalog\Models\Item. name/unit_price
    // are snapshotted at order time so historical orders stay accurate
    // even if the catalog item's price or name later changes — Order
    // never has to read back into Catalog for display.
    protected $fillable = [
        'order_id',
        'item_id',
        'item_name',
        'quantity',
        'unit_price',
        'line_total',
    ];

    protected $casts = [
        'quantity'   => 'integer',
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    protected static function newFactory(): OrderItemFactory
    {
        return OrderItemFactory::new();
    }
}
