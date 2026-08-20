<?php

namespace App\Domain\Cart\Actions;

use App\Domain\Cart\DTOs\CartSnapshotData;
use App\Domain\Cart\Models\DiscountCode;
use App\Domain\Cart\Services\CartRedisRepository;
use App\Shared\Exceptions\DomainException;

class ApplyDiscountCode
{
    public function __construct(
        private readonly CartRedisRepository $cart,
        private readonly GetCart $getCart,
    ) {
    }

    public function __invoke(int $userId, string $code): CartSnapshotData
    {
        $discount = DiscountCode::query()->valid()->where('code', $code)->first();

        if (! $discount) {
            throw new DomainException('کد تخفیف نامعتبر است.', 422, 'invalid_discount_code');
        }

        $this->cart->setDiscountCode($userId, $discount->code);

        return ($this->getCart)($userId);
    }
}
