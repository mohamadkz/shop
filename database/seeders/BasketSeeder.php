<?php

namespace Database\Seeders;

use App\Models\Basket;
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

        foreach ($users as $user) {
            Basket::factory()->create([
                    'user_id' => $user->id
                ]);
        }
    }
}
