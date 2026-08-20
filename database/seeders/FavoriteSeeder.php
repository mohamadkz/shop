<?php

namespace Database\Seeders;

use App\Domain\Catalog\Models\Favorite;
use App\Domain\Catalog\Models\Item;
use App\Domain\Customer\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the `favorites` table — functionally a many-to-many between User
 * and Item, but modeled as its own Favorite entity (Catalog owns it,
 * storing only Customer's user_id as a scalar). There's no belongsToMany()
 * to attach() through here, so pivot rows are inserted directly, with the
 * (user_id, item_id) unique constraint respected by only ever picking each
 * pair once.
 *
 * Depends on UserSeeder + ItemSeeder.
 */
class FavoriteSeeder extends Seeder
{
    /** Roughly how many favorites each seeded user ends up with, on average. */
    private const AVG_FAVORITES_PER_USER = 4;

    public function run(): void
    {
        if (Favorite::query()->exists()) {
            $this->command?->info('Favorites already seeded — skipping.');

            return;
        }

        $userIds = User::query()->pluck('id');
        $itemIds = Item::query()->pluck('id');

        if ($userIds->isEmpty() || $itemIds->isEmpty()) {
            $this->command?->warn('Users or items missing — run UserSeeder and ItemSeeder first. Skipping favorites.');

            return;
        }

        DB::transaction(function () use ($userIds, $itemIds) {
            $rows = [];
            $seenPairs = [];
            $now = now();

            foreach ($userIds as $userId) {
                $favoriteCount = min(fake()->numberBetween(0, self::AVG_FAVORITES_PER_USER * 2), $itemIds->count());

                foreach ($itemIds->random($favoriteCount) as $itemId) {
                    $pairKey = "{$userId}:{$itemId}";

                    // Guards the unique(user_id, item_id) constraint without
                    // relying on the DB to silently skip/throw on collision.
                    if (isset($seenPairs[$pairKey])) {
                        continue;
                    }
                    $seenPairs[$pairKey] = true;

                    $rows[] = [
                        'user_id'    => $userId,
                        'item_id'    => $itemId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            // Bulk insert: hundreds of simple pivot-style rows don't need
            // per-row Eloquent event overhead.
            foreach (array_chunk($rows, 500) as $chunk) {
                DB::table('favorites')->insert($chunk);
            }
        });

        $this->command?->info(sprintf('Seeded %d favorites.', Favorite::query()->count()));
    }
}
