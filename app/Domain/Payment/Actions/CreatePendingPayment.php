<?php

namespace App\Domain\Payment\Actions;

use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Models\Payment;

/**
 * Reacts (via a Listener) to Order\Events\OrderStockConfirmed. Creates the
 * Pending payment shell ahead of time so InitiatePayment (triggered by the
 * client hitting "pay now") has something to attach a gateway authority to.
 * Idempotent on order_id.
 */
class CreatePendingPayment
{
    public function __invoke(int $orderId, int $userId, float $amount): void
    {
        Payment::firstOrCreate(
            ['order_id' => $orderId],
            [
                'user_id'        => $userId,
                'amount'         => $amount,
                'payment_method' => 'zarinpal',
                'status'         => PaymentStatus::Pending,
            ]
        );
    }
}
