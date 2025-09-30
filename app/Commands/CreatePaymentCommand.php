<?php

namespace App\Commands;

use App\Enums\PaymentProvider;

readonly class CreatePaymentCommand
{
    public function __construct(
        public int $project_id,
        public float $amount,
        public string $currency,
        public string $description,
        public ?string $customer_email = null,
        public ?string $customer_phone = null,
        public ?string $customer_key = null,
        public ?string $success_url = null,
        public ?string $fail_url = null,
        public PaymentProvider $payment_provider = PaymentProvider::TBANK,
        public bool $is_recurrent = false,
        public ?string $rebill_id = null,
    ) {}
}
