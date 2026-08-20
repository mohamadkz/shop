<?php

namespace App\Domain\Payment\DTOs;

final class GatewayVerifyResultData
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $refId = null,
        public readonly ?string $maskedCardPan = null,
        public readonly ?string $failureReason = null,
    ) {
    }
}
