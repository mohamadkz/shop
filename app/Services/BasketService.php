<?php

namespace App\Services;

use App\Models\Basket;
use App\Models\BasketItem;
use App\Models\DiscountCode;
use App\Models\Item;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BasketService
{
    private const TTL_DAYS = 7;

    private const LOCK_SECONDS = 5;

    private function itemsKey(int $userId): string
    {
        return "basket:{$userId}:items";
    }

    private function discountKey(int $userId): string
    {
        return "basket:{$userId}:discount_code_id";
    }

    private function lockKey(int $userId): string
    {
        return "basket-lock:{$userId}";
    }

    public function addItem(User $user, Item $item, int $quantity = 1): array
    {
        if ($quantity < 1) {
            throw ValidationException::withMessages(['quantity' => 'Quantity must be at least 1.']);
        }

        if ($item->stock < $quantity) {
            throw ValidationException::withMessages(['quantity' => 'Insufficient stock available.']);
        }

        return Cache::lock($this->lockKey($user->id), self::LOCK_SECONDS)->block(3, function () use ($user, $item, $quantity) {
            $items = $this->getItems($user);

            if (isset($items[$item->id])) {
                $newQty = $items[$item->id]['quantity'] + $quantity;

                if ($item->stock < $newQty) {
                    throw ValidationException::withMessages(['quantity' => 'Insufficient stock available.']);
                }

                $items[$item->id]['quantity'] = $newQty;
            } else {
                $items[$item->id] = [
                    'item_id'  => $item->id,
                    'quantity' => $quantity,
                    'price'    => $item->price,
                ];
            }

            $this->putItems($user, $items);

            return $this->getBasket($user);
        });
    }

    public function updateItemQuantity(User $user, int $itemId, int $quantity): array
    {
        return Cache::lock($this->lockKey($user->id), self::LOCK_SECONDS)->block(3, function () use ($user, $itemId, $quantity) {
            $items = $this->getItems($user);

            if (!isset($items[$itemId])) {
                throw ValidationException::withMessages(['item' => 'Item not found in basket.']);
            }

            if ($quantity < 1) {
                unset($items[$itemId]);
            } else {
                $item = Item::find($itemId);
                if ($item && $item->stock < $quantity) {
                    throw ValidationException::withMessages(['quantity' => 'Insufficient stock available.']);
                }
                $items[$itemId]['quantity'] = $quantity;
            }

            $this->putItems($user, $items);

            return $this->getBasket($user);
        });
    }

    public function removeItem(User $user, int $itemId): array
    {
        return Cache::lock($this->lockKey($user->id), self::LOCK_SECONDS)->block(3, function () use ($user, $itemId) {
            $items = $this->getItems($user);
            unset($items[$itemId]);
            $this->putItems($user, $items);

            return $this->getBasket($user);
        });
    }

    public function applyDiscountCode(User $user, string $code): array
    {
        $discount = DiscountCode::where('code', $code)
            ->where(function ($q) {
                $q->whereNull('expired_at')->orWhere('expired_at', '>', now());
            })
            ->first();

        if (!$discount) {
            throw ValidationException::withMessages(['code' => 'Invalid or expired discount code.']);
        }

        Cache::put($this->discountKey($user->id), $discount->id, now()->addDays(self::TTL_DAYS));

        return $this->getBasket($user);
    }

    public function removeDiscountCode(User $user): array
    {
        Cache::forget($this->discountKey($user->id));

        return $this->getBasket($user);
    }

    public function getBasket(User $user): array
    {
        $items = $this->getItems($user);
        $discount = $this->getDiscount($user);

        $amount = collect($items)->sum(fn ($i) => $i['price'] * $i['quantity']);

        $discountAmount = 0;
        if ($discount) {
            $discountAmount = ($amount * $discount->percent) / 100;
            if ($discount->max_discount) {
                $discountAmount = min($discountAmount, $discount->max_discount);
            }
        }

        return [
            'items'           => array_values($items),
            'discount_code'   => $discount?->code,
            'amount'          => round($amount, 2),
            'discount_amount' => round($discountAmount, 2),
            'total_amount'    => round(max(0, $amount - $discountAmount), 2),
        ];
    }

    public function clearBasket(User $user): void
    {
        Cache::forget($this->itemsKey($user->id));
        Cache::forget($this->discountKey($user->id));
    }

    private function getItems(User $user): array
    {
        return Cache::get($this->itemsKey($user->id), []);
    }

    private function getDiscount(User $user): ?DiscountCode
    {
        $id = Cache::get($this->discountKey($user->id));

        return $id ? DiscountCode::find($id) : null;
    }

    private function putItems(User $user, array $items): void
    {
        if (empty($items)) {
            Cache::forget($this->itemsKey($user->id));
            return;
        }

        Cache::put($this->itemsKey($user->id), $items, now()->addDays(self::TTL_DAYS));
    }

    public function checkout(User $user, string $address): Order
    {
        $cart = $this->getBasket($user);

        if (empty($cart['items'])) {
            throw ValidationException::withMessages(['basket' => 'Your basket is empty.']);
        }

        return DB::transaction(function () use ($user, $cart, $address) {

            $itemIds = collect($cart['items'])->pluck('item_id');

            $dbItems = Item::whereIn('id', $itemIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($cart['items'] as $line) {
                $dbItem = $dbItems->get($line['item_id']);

                if (!$dbItem || $dbItem->stock < $line['quantity']) {
                    throw ValidationException::withMessages([
                        'stock' => "Insufficient stock for \"{$dbItem?->name}\".",
                    ]);
                }
            }

            $discountId = Cache::get($this->discountKey($user->id));

            $basket = Basket::create([
                'user_id'           => $user->id,
                'discount_code_id'  => $discountId,
                'status'            => true,
                'total_amount'      => $cart['amount'],
                'discount_amount'   => $cart['discount_amount'],
                'amount'            => $cart['total_amount'],
            ]);

            $now = now();
            $rows = collect($cart['items'])->map(fn ($line) => [
                'basket_id'  => $basket->id,
                'item_id'    => $line['item_id'],
                'quantity'   => $line['quantity'],
                'price'      => $line['price'],
                'created_at' => $now,
                'updated_at' => $now,
            ])->all();

            BasketItem::insert($rows);

            foreach ($cart['items'] as $line) {
                $dbItems->get($line['item_id'])->decrement('stock', $line['quantity']);
            }

            $order = Order::create([
                'user_id'     => $user->id,
                'basket_id'   => $basket->id,
                'total_price' => $cart['total_amount'],
                'address'     => $address,
                'status'      => OrderStatus::Pending,
            ]);

            Payment::create([
                'order_id'       => $order->id,
                'user_id'        => $user->id,
                'amount'         => $order->total_price,
                'payment_method' => 'zarinpal',
                'status'         => PaymentStatus::Pending,
            ]);

            $this->clearBasket($user);

            return $order->load('payment');
        });
    }
}