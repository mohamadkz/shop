<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Basket;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $baskets = Basket::with('basketItems')->get();

        $orders = [];

        $statuses = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];

        foreach ($baskets as $basket) {
            $total = $basket->basketItems->sum(function ($row) {
                return $row->price * $row->quantity;
            });

            $orders[] = [
                'user_id'     => $basket->user_id,
                'basket_id'   => $basket->id,
                'total_price' => $total,
                'address'     => fake()->address(),
                'status'      => $statuses[array_rand($statuses)],
                'created_at'  => now(),
                'updated_at'  => now(),
            ];
        }

        Order::insert($orders);
    }
}
