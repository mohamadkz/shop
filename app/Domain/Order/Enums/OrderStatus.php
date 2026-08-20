<?php

namespace App\Domain\Order\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';

    case AwaitingPayment = 'awaiting_payment';

    case Paid = 'paid';

    case Processing = 'processing';

    case Completed = 'completed';

    case Cancelled = 'cancelled';

    case Failed = 'failed';

    case Shipped = 'shipped';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'در انتظار بررسی موجودی',
            self::AwaitingPayment => 'در انتظار پرداخت',
            self::Paid => 'پرداخت شده',
            self::Processing => 'در حال پردازش',
            self::Shipped => 'ارسال شده',
            self::Completed => 'تکمیل شده',
            self::Cancelled => 'لغو شده',
            self::Failed => 'ناموفق',
        };
    }

    public function isPending(): bool
    {
        return $this === self::Pending;
    }

    public function isAwaitingPayment(): bool
    {
        return $this === self::AwaitingPayment;
    }

    public function isCancellable(): bool
    {
        return in_array($this, [self::Pending, self::AwaitingPayment], true);
    }
}
