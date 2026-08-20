<?php

namespace Database\Seeders;

use App\Domain\Catalog\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds Catalog categories. Runs before ItemSeeder, since every item
 * requires a valid category_id.
 *
 * Categories are self-referential (parent_id -> categories.id), so this
 * seeder does two passes: all top-level categories first (parent_id null),
 * then a batch of children pointing at those already-persisted parents —
 * a child can never be created before its parent has an id.
 */
class CategorySeeder extends Seeder
{
    /** Curated names instead of pure Faker output, so ItemSeeder can group items sensibly. */
    private const TOP_LEVEL = [
        'Electronics',
        'Home & Kitchen',
        'Fashion',
        'Books',
        'Sports & Outdoors',
    ];

    private const CHILDREN_PER_PARENT = 3;

    public function run(): void
    {
        if (Category::query()->exists()) {
            $this->command?->info('Categories already seeded — skipping.');

            return;
        }

        DB::transaction(function () {
            foreach (self::TOP_LEVEL as $name) {
                $parent = Category::factory()->create([
                    'name' => $name,
                    'slug' => \Illuminate\Support\Str::slug($name),
                ]);

                Category::factory()
                    ->count(self::CHILDREN_PER_PARENT)
                    ->childOf($parent)
                    ->create();
            }
        });

        $total = count(self::TOP_LEVEL) * (1 + self::CHILDREN_PER_PARENT);
        $this->command?->info("Seeded {$total} categories (" . count(self::TOP_LEVEL) . ' parents + children).');
    }
}
