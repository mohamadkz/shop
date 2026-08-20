<?php

namespace App\Domain\Cart\Actions;

use App\Domain\Cart\DTOs\CartSnapshotData;
use App\Domain\Cart\Services\CartRedisRepository;
use App\Shared\Services\DistributedLock;

class RemoveItemFromCart
{
    public function __construct(
        private readonly CartRedisRepository $cart,
        private readonly GetCart $getCart,
        private readonly DistributedLock $lock,
    ) {
    }

    public function __invoke(int $userId, int $itemId): CartSnapshotData
    {
        return $this->lock->run($this->cart->lockKey($userId), function () use ($userId, $itemId) {
            $this->cart->removeLine($userId, $itemId);

            return ($this->getCart)($userId);
        });
    }
}
