<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Order;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orders = Order::all();

        if ($orders->isEmpty()) {
            return;
        }

        $payments = [];

        foreach ($orders as $order) {
            if ($order->status === 'cancelled') {
                $paymentStatus = 'failed';
            } elseif ($order->status === 'completed' || $order->status === 'shipped' || $order->status === 'processing') {
                $paymentStatus = 'success';
            } else {
                $paymentStatus = 'pending';
            }
            $payments[] = [
                'order_id'       => $order->id,
                'amount'         => $order->total_price,
                'payment_method' => fake()->randomElement(['ZarinPal', 'PayPal', 'Wallet']),
                'transaction_id' => fake()->uuid(),
                'status'         => $paymentStatus,
                'paid_at'        => now(),
                'created_at'     => now(),
                'updated_at'     => now(),
            ];
        }

        Payment::insert($payments);
    }
}
