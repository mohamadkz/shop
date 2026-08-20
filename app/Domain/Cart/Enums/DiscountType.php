<?php

namespace App\Domain\Cart\Enums;

enum DiscountType: string
{
    case Percentage = 'percentage';
    case Fixed = 'fixed';
}
