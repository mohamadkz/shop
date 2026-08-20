<?php

namespace App\Mail;

use App\Domain\Payment\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Payment $payment,
    ) {
    }

    public function build(): self
    {
        return $this
            ->subject('پرداخت شما با موفقیت انجام شد')
            ->view('email.payment-success');
    }
}