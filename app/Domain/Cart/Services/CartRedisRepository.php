<?php

namespace App\Domain\Cart\Services;

use App\Domain\Cart\DTOs\CartLineData;
use Illuminate\Support\Facades\Redis;

/**
 * Redis-backed cart persistence. Uses a Redis HASH per user (one field per
 * item_id) so individual line mutations are O(1) and don't require
 * read-modify-write of a single serialized blob under the shared lock —
 * the lock in the Actions still guards multi-step read+validate+write
 * sequences, but storage itself stays atomic per field.
 */
class CartRedisRepository
{
    private const TTL_SECONDS = 7 * 24 * 60 * 60; 
    public function itemsKey(int $userId): string
    {
        return "cart:{$userId}:items";
    }

    public function discountKey(int $userId): string
    {
        return "cart:{$userId}:discount_code";
    }

    public function lockKey(int $userId): string
    {
        return "cart-lock:{$userId}";
    }

    /**
     * @return CartLineData[] keyed by item_id
     */
    public function getLines(int $userId): array
    {
        $raw = Redis::hgetall($this->itemsKey($userId));

        $lines = [];
        foreach ($raw as $itemId => $json) {
            $decoded = json_decode($json, true);
            if (is_array($decoded)) {
                $lines[(int) $itemId] = CartLineData::fromArray($decoded);
            }
        }

        return $lines;
    }

    public function putLine(int $userId, CartLineData $line): void
    {
        $key = $this->itemsKey($userId);

        Redis::hset($key, (string) $line->itemId, json_encode($line->toArray()));
        Redis::expire($key, self::TTL_SECONDS);
    }

    public function removeLine(int $userId, int $itemId): void
    {
        Redis::hdel($this->itemsKey($userId), (string) $itemId);
    }

    public function setDiscountCode(int $userId, string $code): void
    {
        Redis::setex($this->discountKey($userId), self::TTL_SECONDS, $code);
    }

    public function getDiscountCode(int $userId): ?string
    {
        $code = Redis::get($this->discountKey($userId));

        return $code !== false && $code !== null ? $code : null;
    }

    public function removeDiscountCode(int $userId): void
    {
        Redis::del($this->discountKey($userId));
    }

    public function clear(int $userId): void
    {
        Redis::del($this->itemsKey($userId), $this->discountKey($userId));
    }
}
