<?php

namespace App\Commands;

use App\Enums\PaymentProvider;

readonly class CreateSubscriptionCommand
{
    public function __construct(
        public int $project_id,
        public string $email,
        public ?string $name,
        public float $amount,
        public string $currency,
        public string $description,
        public ?string $success_url = null,
        public ?string $fail_url = null,
        public PaymentProvider $payment_provider = PaymentProvider::TBANK,
    ) {}
}
