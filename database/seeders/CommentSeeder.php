<?php

namespace Database\Seeders;

use App\Domain\Catalog\Models\Comment;
use App\Domain\Catalog\Models\Item;
use App\Domain\Customer\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds Catalog comments. Depends on UserSeeder and ItemSeeder — a comment
 * needs a real user_id and item_id. Comment doesn't declare an Eloquent
 * relation to User (Catalog never references Customer's model directly),
 * so we resolve real ids here in the seeder and pass them as plain
 * integers, matching how the domain itself only ever stores the id.
 */
class CommentSeeder extends Seeder
{
    private const COMMENTS_TO_CREATE = 15;

    public function run(): void
    {
        if (Comment::query()->exists()) {
            $this->command?->info('Comments already seeded — skipping.');

            return;
        }

        $userIds = User::query()->pluck('id');
        $itemIds = Item::query()->pluck('id');

        if ($userIds->isEmpty() || $itemIds->isEmpty()) {
            $this->command?->warn('Users or items missing — run UserSeeder and ItemSeeder first. Skipping comments.');

            return;
        }

        DB::transaction(function () use ($userIds, $itemIds) {
            for ($i = 0; $i < self::COMMENTS_TO_CREATE; $i++) {
                Comment::factory()->create([
                    'user_id' => $userIds->random(),
                    'item_id' => $itemIds->random(),
                ]);
            }
        });

        $this->command?->info(sprintf('Seeded %d comments.', self::COMMENTS_TO_CREATE));
    }
}
