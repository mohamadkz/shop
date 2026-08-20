<?php

namespace App\Shared\Exceptions;

class InsufficientStockException extends DomainException
{
    public function __construct(public readonly int $itemId, string $itemName)
    {
        parent::__construct(
            message: "موجودی کالا \"{$itemName}\" کافی نیست.",
            status: 409,
            errorCode: 'insufficient_stock'
        );
    }
}
