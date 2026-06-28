<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\User;
use App\Models\Item;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userIds = User::pluck('id')->toArray();

        if (empty($userIds)) {
            return;
        }

        $items = Item::all();
        $comments = [];

        foreach ($items as $item) {

            $randomUserId = $userIds[array_rand($userIds)];

            $comments[] = [
                'user_id'    => $randomUserId,
                'item_id'    => $item->id,
                'rating'     => rand(1, 5),
                'comment'    => fake()->paragraph(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Comment::insert($comments);
    }
}
