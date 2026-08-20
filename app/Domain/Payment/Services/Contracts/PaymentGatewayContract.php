<?php

namespace App\Domain\Payment\Services\Contracts;

use App\Domain\Payment\DTOs\GatewayVerifyResultData;

interface PaymentGatewayContract
{
    /**
     * Requests a payment session from the gateway and returns its
     * authority/transaction token to store against the Payment row.
     */
    public function requestPayment(float $amount, string $callbackUrl, string $description, string $orderNumber): string;

    /**
     * Returns the URL the client should be redirected to in order to
     * complete payment on the gateway's hosted page.
     */
    public function redirectUrl(string $authority): string;

    /**
     * Server-side verification against the gateway. NEVER trust the
     * callback's query params alone — always confirm with the gateway
     * directly using its verify API before marking a payment successful.
     */
    public function verify(string $authority, float $amount): GatewayVerifyResultData;
}
