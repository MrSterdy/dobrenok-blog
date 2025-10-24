<?php

namespace App\Commands;

readonly class UpdatePaymentCommand
{
    public function __construct(
        public int $payment_id,
        public array $attributes,
    ) {}
}
