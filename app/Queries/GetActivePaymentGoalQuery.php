<?php

namespace App\Queries;

readonly class GetActivePaymentGoalQuery
{
    public function __construct(
        public int $project_id,
    ) {}
}
