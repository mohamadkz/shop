<?php

namespace Database\Seeders;

use App\Models\Basket;
use App\Models\DiscountCode;
use App\Models\BasketItem;
use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BasketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::inRandomOrder()->take(30)->get();
        // $items = Item::all();
        $discountCodes = DiscountCode::all();

        foreach ($users as $user) {
            Basket::factory()->create([
                'user_id' => $user->id,
                'status'         => 1,
                'discount_code_id' => fake()->boolean(50) ? $discountCodes->random()->id : null,
                'total_amount'   => 0,
                'discount_amount' => 0,
                'amount'         => 0,
            ]);
        }
    }
}
