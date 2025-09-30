<?php

namespace App\DTOs;

readonly class PaymentCreationResultDTO
{
    public function __construct(
        public string $external_payment_id,
        public string $status,
        public ?string $payment_url = null,
        public array $additional_data = [],
    ) {}
}
