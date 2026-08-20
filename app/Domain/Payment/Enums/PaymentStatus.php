<?php

namespace App\Domain\Payment\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';

    case Success = 'success';

    case Failed = 'failed';

    case Cancelled = 'cancelled';

    public function isPending(): bool
    {
        return $this === self::Pending;
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Success, self::Failed, self::Cancelled], true);
    }
}
