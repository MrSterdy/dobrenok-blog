<?php

namespace App\Commands;

readonly class AddPaymentToGoalCommand
{
    public function __construct(
        public int $project_id,
        public float $amount,
        public string $currency,
    ) {}
}
