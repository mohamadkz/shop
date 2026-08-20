<?php

namespace App\Domain\Cart\Actions;

use App\Domain\Cart\DTOs\CartSnapshotData;
use App\Domain\Cart\Models\DiscountCode;
use App\Domain\Cart\Services\CartPricingCalculator;
use App\Domain\Cart\Services\CartRedisRepository;

class GetCart
{
    public function __construct(
        private readonly CartRedisRepository $cart,
        private readonly CartPricingCalculator $pricing,
    ) {
    }

    public function __invoke(int $userId): CartSnapshotData
    {
        $lines = $this->cart->getLines($userId);

        $discountCode = $this->cart->getDiscountCode($userId);
        $discount = $discountCode
            ? DiscountCode::query()->valid()->where('code', $discountCode)->first()
            : null;

        // Discount became invalid (expired/exhausted) since it was applied — drop it silently.
        if ($discountCode && ! $discount) {
            $this->cart->removeDiscountCode($userId);
        }

        return $this->pricing->calculate($lines, $discount);
    }
}
