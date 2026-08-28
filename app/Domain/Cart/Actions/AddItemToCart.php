<?php

namespace App\Domain\Cart\Actions;

use App\Domain\Cart\DTOs\CartLineData;
use App\Domain\Cart\DTOs\CartSnapshotData;
use App\Domain\Cart\Services\CartRedisRepository;
use App\Shared\Exceptions\DomainException;
use App\Shared\Services\DistributedLock;

/**
 * Note: Cart intentionally does NOT verify live stock against Catalog's
 * Item table here — that would require calling into another domain. A
 * generous client-side/display-time stock hint can come from a read model
 * (e.g. a cached projection), but the authoritative check happens once,
 * atomically, in Catalog\Actions\DecrementStockForOrder during checkout.
 */
class AddItemToCart
{
    private const MAX_QUANTITY_PER_LINE = 999;

    public function __construct(
        private readonly CartRedisRepository $cart,
        private readonly GetCart $getCart,
        private readonly DistributedLock $lock,
    ) {
    }

    public function __invoke(int $userId, int $itemId, int $quantity, float $unitPrice): CartSnapshotData
    {
        if ($quantity < 1 || $quantity > self::MAX_QUANTITY_PER_LINE) {
            throw new DomainException('مقدار نامعتبر است.', 422, 'invalid_quantity');
        }

        return $this->lock->run($this->cart->lockKey($userId), function () use ($userId, $itemId, $quantity, $unitPrice) {
            $lines = $this->cart->getLines($userId);
            $existing = $lines[$itemId] ?? null;

            $newLine = new CartLineData(
                itemId: $itemId,
                quantity: ($existing?->quantity ?? 0) + $quantity,
                unitPrice: $unitPrice,
            );

            if ($newLine->quantity > self::MAX_QUANTITY_PER_LINE) {
                throw new DomainException('مقدار نامعتبر است.', 422, 'invalid_quantity');
            }

            $this->cart->putLine($userId, $newLine);

            return ($this->getCart)($userId);
        });
    }
}
