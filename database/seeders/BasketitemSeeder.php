<?php

namespace Database\Seeders;

use App\Models\BasketItem;
use App\Models\Basket;
use App\Models\Item;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BasketitemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $baskets = Basket::all();
        $items = Item::all();

        if ($baskets->isEmpty() || $items->isEmpty()) {
            $this->command->error("خطا: ابتدا باید item و Basket را ایجاد کنید!");
            return;
        }

        foreach ($baskets as $basket) {
            $count = rand(2, 5);
            $selectedItems = $items->random($count);

            $dataToInsert = [];

            foreach ($selectedItems as $item) {
                $dataToInsert[] = [
                    'basket_id'  => $basket->id,
                    'item_id'    => $item->id,
                    'quantity'   => rand(1, 3),
                    'price'      => $item->price,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            BasketItem::insert($dataToInsert);
            $basket->refresh();
            $basket->calculateTotals();
        }
    }
}
