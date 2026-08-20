<?php

namespace App\Domain\Catalog\Enums;

enum ItemStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Archived = 'archived';

    public function isPurchasable(): bool
    {
        return $this === self::Active;
    }
}
