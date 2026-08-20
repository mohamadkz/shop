<?php

namespace Database\Seeders\Support;

use App\Domain\Cart\Models\DiscountCode;
use App\Domain\Catalog\Models\Item;
use App\Domain\Customer\Models\User;
use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Models\Payment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Builds one complete, status-consistent Order aggregate for seeding.
 *
 * This intentionally does NOT go through Order\Actions\PlaceOrder or
 * dispatch OrderPlaced: seeding must not trigger the real saga (queued
 * listeners, gateway calls, SMS/email sends). It reads across Catalog /
 * Customer / Order / Payment directly instead, which is fine here — this
 * class is dev/test tooling that runs outside the request/event pipeline,
 * not a domain Action bound by the "domains never call each other"
 * production rule described in the project README.
 *
 * Every table this touches is written in a single DB transaction so a
 * partially-built order can never survive a failure mid-way through.
 */
class OrderSeedingService
{
    private const TAX_RATE = 0.09;

    public function __construct(
        /** @var Collection<int, Item> */
        private readonly Collection $catalogItems,
        /** @var Collection<int, DiscountCode> */
        private readonly Collection $discountCodes,
    ) {
    }

    /**
     * @param  OrderStatus  $targetStatus  Determines which downstream rows
     *                                     (stock reservation, payment) get
     *                                     created alongside the order, so
     *                                     the seeded data never contains an
     *                                     impossible state (e.g. a Paid
     *                                     order with no Payment row).
     */
    public function seedOrder(User $user, OrderStatus $targetStatus): Order
    {
        return DB::transaction(function () use ($user, $targetStatus) {
            $lines = $this->pickRandomLines();
            $discount = fake()->boolean(25) ? $this->discountCodes->random() : null;

            $subtotal = round($lines->sum(fn (array $l) => $l['quantity'] * $l['unit_price']), 2);
            $discountAmount = $discount ? $discount->calculateDiscount($subtotal) : 0.0;
            $taxable = max(0, $subtotal - $discountAmount);
            $tax = round($taxable * self::TAX_RATE, 2);
            $total = round($taxable + $tax, 2);

            $order = Order::create([
                'user_id'         => $user->id,
                'uuid'            => (string) Str::uuid(),
                'order_number'    => 'ORD-' . now()->format('Ymd') . '-' . strtoupper(fake()->bothify('??####')),
                'idempotency_key' => (string) Str::uuid(),
                'subtotal'        => $subtotal,
                'discount_amount' => $discountAmount,
                'discount_code'   => $discount?->code,
                'tax'             => $tax,
                'total_price'     => $total,
                'address'         => fake()->address(),
                'status'          => OrderStatus::Pending, // advanced below once items exist
            ]);

            foreach ($lines as $line) {
                $order->items()->create([
                    'item_id'    => $line['item_id'],
                    'item_name'  => $line['item_name'],
                    'quantity'   => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'line_total' => round($line['quantity'] * $line['unit_price'], 2),
                ]);
            }

            $this->advanceToStatus($order, $targetStatus, $lines);

            return $order->fresh('items');
        });
    }

    /**
     * Mimics the effects the real saga's listeners would have already
     * produced by the time an order reaches $targetStatus, without
     * actually dispatching events. Reservation rows only exist for
     * statuses at or past AwaitingPayment, matching production behaviour.
     */
    private function advanceToStatus(Order $order, OrderStatus $targetStatus, Collection $lines): void
    {
        if ($targetStatus === OrderStatus::Pending) {
            return;
        }

        if ($targetStatus === OrderStatus::Cancelled) {
            // Simulates a stock-reservation failure: order goes straight
            // from Pending to Cancelled, no reservation/payment ever existed.
            $order->update(['status' => OrderStatus::Cancelled]);

            return;
        }

        // AwaitingPayment, Paid, Processing, Shipped, Completed all imply
        // stock was successfully reserved: decrement stock + record the
        // reservation ledger, exactly like Catalog\DecrementStockForOrder does.
        DB::table('catalog_stock_reservations')->insert([
            'order_id'       => $order->id,
            'reserved_lines' => json_encode($lines->map(fn ($l) => [
                'item_id'  => $l['item_id'],
                'quantity' => $l['quantity'],
            ])->all()),
            'created_at'     => now(),
        ]);

        foreach ($lines as $line) {
            Item::whereKey($line['item_id'])->decrement('stock', $line['quantity']);
        }

        $order->update(['status' => OrderStatus::AwaitingPayment]);

        if ($targetStatus === OrderStatus::AwaitingPayment) {
            Payment::factory()->for($order)->create([
                'user_id' => $order->user_id,
                'amount'  => $order->total_price,
            ]);

            return;
        }

        // Paid / Processing / Shipped / Completed all imply a successful payment.
        Payment::factory()->successful()->for($order)->create([
            'user_id' => $order->user_id,
            'uuid'    => (string) Str::uuid(),
            'amount'  => $order->total_price,
        ]);

        $order->update(['status' => $targetStatus]);
    }

    /**
     * @return Collection<int, array{item_id:int, item_name:string, quantity:int, unit_price:float}>
     */
    private function pickRandomLines(): Collection
    {
        return $this->catalogItems
            ->random(min(fake()->numberBetween(1, 4), $this->catalogItems->count()))
            ->map(fn (Item $item) => [
                'item_id'    => $item->id,
                'item_name'  => $item->name,
                'quantity'   => fake()->numberBetween(1, 3),
                'unit_price' => (float) $item->price,
            ])
            ->values();
    }
}
