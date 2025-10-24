<?php

namespace App\Commands;

readonly class CreateProjectPaymentGoalCommand
{
    public function __construct(
        public int $project_id,
        public float $target_amount,
        public string $currency,
        public ?string $description = null,
        public ?\DateTime $deadline = null,
    ) {}
}
