<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            ItemSeeder::class,
            DiscountCodeSeeder::class,
            BasketSeeder::class,
            BasketItemSeeder::class,
            OrderSeeder::class,
            PaymentSeeder::class,
            FollowupSeeder::class,
            CommentSeeder::class,
            FavoriteSeeder::class

        ]);
    }
}
