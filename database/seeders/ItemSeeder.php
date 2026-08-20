<?php

namespace Database\Seeders;

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Item;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds Catalog items. Depends on CategorySeeder having already run —
 * every item needs a real category_id. Also runs before Comment/Favorite/
 * Order seeders, all of which reference item_id.
 */
class ItemSeeder extends Seeder
{
    private const ITEMS_PER_CATEGORY = 8;
    private const OUT_OF_STOCK_RATIO = 10; // percent

    public function run(): void
    {
        if (Item::query()->exists()) {
            $this->command?->info('Items already seeded — skipping.');

            return;
        }

        $categories = Category::query()->whereNotNull('parent_id')->get();

        if ($categories->isEmpty()) {
            $this->command?->warn('No child categories found — run CategorySeeder first. Skipping items.');

            return;
        }

        DB::transaction(function () use ($categories) {
            foreach ($categories as $category) {
                Item::factory()
                    ->count(self::ITEMS_PER_CATEGORY)
                    ->create(['category_id' => $category->id]);
            }

            // A handful of out-of-stock items, so oversell-prevention /
            // "sold out" UI paths have real data to exercise.
            $outOfStockIds = Item::query()
                ->inRandomOrder()
                ->limit((int) round(Item::query()->count() * self::OUT_OF_STOCK_RATIO / 100))
                ->pluck('id');

            Item::query()->whereIn('id', $outOfStockIds)->update(['stock' => 0]);
        });

        $this->command?->info(sprintf('Seeded %d items across %d categories.', Item::query()->count(), $categories->count()));
    }
}
