<?php

namespace Database\Seeders;

use App\Domain\Cart\Models\DiscountCode;
use App\Domain\Catalog\Models\Item;
use App\Domain\Customer\Models\User;
use App\Domain\Order\Enums\OrderStatus;
use Database\Seeders\Support\OrderSeedingService;
use Illuminate\Database\Seeder;

/**
 * Seeds Orders together with their OrderItems, and — depending on the
 * target status — a matching catalog_stock_reservations row and/or
 * Payment row. Runs last: it's the only seeder that touches four tables
 * across three domains (Order, Catalog's reservation ledger, Payment),
 * all of which must already have valid Users, Items, and DiscountCodes
 * to reference.
 *
 * OrderItem and Payment don't get their own top-level seeder entries in
 * DatabaseSeeder even though they're separate tables/entities: both are
 * structurally owned by a specific Order and must stay consistent with
 * its status (e.g. a Paid order must have exactly one successful Payment).
 * Seeding them independently would risk generating invalid state
 * combinations, so OrderSeedingService builds each order's full aggregate
 * atomically instead — see its DB::transaction() wrapper.
 */
class OrderSeeder extends Seeder
{
    /**
     * Distribution of seeded orders across the statuses a real order can
     * reach, weighted toward the terminal happy-path (Paid) since that's
     * the most common real-world outcome.
     */
    
    private static function statusWeights(): array
{
    return [
        OrderStatus::Pending->value => 5,
        OrderStatus::Cancelled->value => 10,
        OrderStatus::AwaitingPayment->value => 10,
        OrderStatus::Paid->value => 40,
        OrderStatus::Completed->value => 35,
    ];
}

    public function run(): void
    {
        if (\App\Domain\Order\Models\Order::query()->exists()) {
            $this->command?->info('Orders already seeded — skipping.');

            return;
        }

        $users = User::query()->get();
        $items = Item::query()->where('stock', '>', 1)->get();
        $discountCodes = DiscountCode::query()->valid()->get();

        if ($users->isEmpty() || $items->isEmpty()) {
            $this->command?->warn('Users or in-stock items missing — run UserSeeder and ItemSeeder first. Skipping orders.');

            return;
        }

        $service = new OrderSeedingService($items, $discountCodes);
        $statusPool = $this->buildWeightedStatusPool();

        $ordersCreated = 0;

        foreach ($users->random(min(20, $users->count())) as $user) {
            $ordersForThisUser = fake()->numberBetween(1, 3);

            for ($i = 0; $i < $ordersForThisUser; $i++) {
                $service->seedOrder($user, $statusPool[array_rand($statusPool)]);
                $ordersCreated++;
            }
        }

        $this->command?->info("Seeded {$ordersCreated} orders (with items, reservations, and payments as applicable).");
    }

    /**
     * Expands STATUS_WEIGHTS into a flat pool so a plain array_rand() pick
     * respects the intended proportions without pulling in a stats package.
     *
     * @return OrderStatus[]
     */
    private function buildWeightedStatusPool(): array
    {
        $pool = [];
        $weights = self::statusWeights();

        foreach ( $weights as $status => $weight) {
            $statusEnum = OrderStatus::from($status);
            $pool = array_merge($pool, array_fill(0, $weight, $statusEnum));
        }

        return $pool;
    }
}
