<?php

namespace App\Domain\Payment\Actions;

use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Events\PaymentFailed;
use App\Domain\Payment\Events\PaymentSucceeded;
use App\Domain\Payment\Models\Payment;
use App\Domain\Payment\Services\Contracts\PaymentGatewayContract;
use App\Shared\Exceptions\DomainException;
use App\Shared\Services\DistributedLock;
use Illuminate\Support\Facades\DB;

/**
 * Handles the gateway's redirect-back callback. Client-supplied query
 * params (Authority, Status) are NEVER trusted on their own — success is
 * only ever concluded from the gateway's own server-to-server verify()
 * response, inside a locked, transactional, idempotent block.
 */
class HandlePaymentCallback
{
    public function __construct(
        private readonly PaymentGatewayContract $gateway,
        private readonly DistributedLock $lock,
    ) {
    }

    public function __invoke(string $authority, bool $clientReportedOk): Payment
    {
        return $this->lock->run("payment-callback:{$authority}", function () use ($authority, $clientReportedOk) {
            return DB::transaction(function () use ($authority, $clientReportedOk) {
                $payment = Payment::where('authority', $authority)->lockForUpdate()->first();

                if (! $payment) {
                    throw new DomainException('پرداخت یافت نشد.', 404, 'payment_not_found');
                }

                // Idempotency: already-processed callbacks (browser back
                // button, gateway retry) are safely re-answered without
                // re-verifying or re-dispatching events.
                if ($payment->status->isTerminal()) {
                    return $payment;
                }

                if (! $clientReportedOk) {
                    return $this->fail($payment, 'client_reported_failure');
                }

                $result = $this->gateway->verify($authority, (float) $payment->amount);

                if (! $result->success) {
                    return $this->fail($payment, $result->failureReason ?? 'gateway_verification_failed');
                }

                $payment->update([
                    'status'   => PaymentStatus::Success,
                    'ref_id'   => $result->refId,
                    'card_pan' => $result->maskedCardPan,
                    'paid_at'  => now(),
                ]);

                PaymentSucceeded::dispatch($payment->id, $payment->order_id, $payment->user_id, (float) $payment->amount);

                return $payment;
            });
        }, ttlSeconds: 15, waitSeconds: 10);
    }

    private function fail(Payment $payment, string $reason): Payment
    {
        $payment->update(['status' => PaymentStatus::Failed]);

        PaymentFailed::dispatch($payment->id, $payment->order_id, $payment->user_id, $reason);

        return $payment;
    }
}
