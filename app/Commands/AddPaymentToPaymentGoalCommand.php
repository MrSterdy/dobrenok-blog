<?php

namespace App\Commands;

readonly class AddPaymentToPaymentGoalCommand
{
    public function __construct(
        public int $goal_id,
        public float $amount,
    ) {}
}
