<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\DiscountCode;
use Illuminate\Database\Seeder;
use Carbon\Carbon;


class DiscountCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $codes = [
            ['prefix' => 'WELCOME', 'percent' => 10, 'max' => 50000],
            ['prefix' => 'SUMMER',  'percent' => 20, 'max' => 100000],
            ['prefix' => 'VIP',      'percent' => 30, 'max' => 200000],
            ['prefix' => 'FLASH',    'percent' => 50, 'max' => 500000],
        ];

        foreach ($codes as $item) {
            DiscountCode::create([
                'code' => $item['prefix'] . '-' . strtoupper(fake()->bothify('??##')),
                'percent' => $item['percent'],
                'max_discount' => $item['max'],
                'expired_at' => fake()->boolean(30) ? Carbon::now()->subDays(10) : Carbon::now()->addMonths(1),
                'usage_limit' => fake()->numberBetween(5, 100),
            ]);
        }
    }
}
