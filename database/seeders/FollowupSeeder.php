<?php

namespace Database\Seeders;

use App\Models\Followup;
use App\Models\Order;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FollowupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $steps = [
            'pending'    => ['ثبت سفارش'],
            'processing' => ['ثبت سفارش', 'در حال آماده سازی'],
            'shipped'    => ['ثبت سفارش', 'در حال آماده سازی', 'ارسال شد'],
            'completed'  => ['ثبت سفارش', 'در حال آماده سازی', 'ارسال شد', 'تحویل مشتری'],
            'paid' => ['ثبت سفارش', 'در حال آماده سازی', 'ارسال شد', 'تحویل مشتری', 'پرداخت شد'],
            'cancelled'  => ['لغو شد'],

        ];

        $orders = Order::all();

        $followups = [];

        foreach ($orders as $order) {

            $orderSteps = $steps[$order->status->value] ?? ['ثبت سفارش'];

            foreach ($orderSteps as $stepTitle) {
                $followups[] = [
                    'order_id'    => $order->id,
                    'title'       => $stepTitle,
                    'description' => fake()->sentence(),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }
        }

        Followup::insert($followups);
    }
}
