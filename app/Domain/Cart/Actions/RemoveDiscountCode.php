<?php

namespace App\Domain\Cart\Actions;

use App\Domain\Cart\DTOs\CartSnapshotData;
use App\Domain\Cart\Services\CartRedisRepository;

class RemoveDiscountCode
{
    public function __construct(
        private readonly CartRedisRepository $cart,
        private readonly GetCart $getCart,
    ) {
    }

    public function __invoke(int $userId): CartSnapshotData
    {
        $this->cart->removeDiscountCode($userId);

        return ($this->getCart)($userId);
    }
}
