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
            'ثبت سفارش',
            'درحال آماده سازی',
            'ارسال شد'
        ];

        $orders = Order::all();

        $followups = [];

        foreach ($orders as $order) {

            $randomCount = rand(1, count($steps));
            $currentSteps = array_slice($steps, 0, $randomCount);
            $isCancelled = rand(1, 5) === 1;

            if ($isCancelled) {
                $followups[] = [
                'order_id'    => $order->id,
                'title'       => 'لغو شد',
                'description' => fake()->sentence(),
                'created_at'  => now(),
                'updated_at'  => now(),
            ];

            } else {

                foreach ($currentSteps as $step) {
                    $followups[] = [
                        'order_id'    => $order->id,
                        'title'       => $step,
                        'description' => fake()->sentence(),
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ];
                }
            }
        }

        Followup::insert($followups);
    }
}
