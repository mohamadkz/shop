<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Models\Comment;
use App\Domain\Catalog\Models\Item;
use App\Shared\Exceptions\DomainException;

class AddComment
{
    public function __invoke(Item $item, int $userId, int $rating, string $comment): Comment
    {
        if ($rating < 1 || $rating > 5) {
            throw new DomainException('امتیاز باید بین ۱ تا ۵ باشد.', 422, 'invalid_rating');
        }

        return $item->comments()->create([
            'user_id' => $userId,
            'rating'  => $rating,
            'comment' => $comment,
        ]);
    }
}
