<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Item;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoryIds = Category::pluck('id');

        Item::factory(100)->create([
            'category_id' => fn() => $categoryIds->random(),
        ]);
    }
}
