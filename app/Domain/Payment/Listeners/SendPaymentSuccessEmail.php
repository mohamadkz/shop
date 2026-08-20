<?php

namespace App\Domain\Payment\Listeners;

use App\Domain\Payment\Events\PaymentSucceeded;
use App\Mail\PaymentSuccessMail;
use App\Domain\Payment\Models\Payment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\InteractsWithQueue;

class SendPaymentSuccessEmail implements ShouldQueue
{
    public string $queue = 'default';

    public int $tries = 5;

    public int $backoff = 10;

    public function handle(PaymentSucceeded $event): void
    {
        $payment = Payment::with([
            'user',
            'order',
        ])->find($event->paymentId);

        if (! $payment) {
            return;
        }

        if ($payment->status->value !== 'success') {
            return;
        }

        $user = $payment->user;

        if (! $user || ! $user->email) {
            return;
        }

        Mail::to($user->email)
            ->send(new PaymentSuccessMail($payment));
    }
}
