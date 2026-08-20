<?php

namespace App\Domain\Order\Services;

use Illuminate\Support\Str;

class OrderNumberGenerator
{
    public function generate(): string
    {
        // Date prefix keeps numbers sortable/human-scannable; random suffix
        // avoids a hot-row sequence table under concurrent checkouts.
        return 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
    }
}
