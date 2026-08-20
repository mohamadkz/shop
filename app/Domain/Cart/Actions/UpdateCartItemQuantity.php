<?php

namespace App\Domain\Cart\Actions;

use App\Domain\Cart\DTOs\CartLineData;
use App\Domain\Cart\DTOs\CartSnapshotData;
use App\Domain\Cart\Services\CartRedisRepository;
use App\Shared\Exceptions\DomainException;
use App\Shared\Services\DistributedLock;

class UpdateCartItemQuantity
{
    public function __construct(
        private readonly CartRedisRepository $cart,
        private readonly GetCart $getCart,
        private readonly DistributedLock $lock,
    ) {
    }

    public function __invoke(int $userId, int $itemId, int $quantity): CartSnapshotData
    {
        return $this->lock->run($this->cart->lockKey($userId), function () use ($userId, $itemId, $quantity) {
            $lines = $this->cart->getLines($userId);
            $existing = $lines[$itemId] ?? null;

            if (! $existing) {
                throw new DomainException('کالا در سبد خرید یافت نشد.', 404, 'cart_item_not_found');
            }

            if ($quantity < 1) {
                $this->cart->removeLine($userId, $itemId);
            } else {
                $this->cart->putLine($userId, new CartLineData($itemId, $quantity, $existing->unitPrice));
            }

            return ($this->getCart)($userId);
        });
    }
}
