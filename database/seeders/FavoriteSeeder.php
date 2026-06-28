<?php

namespace Database\Seeders;

use App\Models\Favorite;
use App\Models\User;
use App\Models\Item;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userIds = User::pluck('id')->toArray();
        $itemIds = Item::pluck('id')->toArray();

        if (empty($userIds) || empty($itemIds)) {
            return;
        }

        $favorites = [];

        foreach ($userIds as $userId) {

            $randomKeys = array_rand($itemIds, min(5, count($itemIds)));

            foreach ($randomKeys as $key) {

                $favorites[] = [
                    'user_id'    => $userId,
                    'item_id'    => $itemIds[$key],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        Favorite::insert($favorites);
    }
}
