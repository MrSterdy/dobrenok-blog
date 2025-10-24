<?php

namespace App\Queries;

readonly class GetPaymentGoalByIdQuery
{
    public function __construct(
        public int $id,
    ) {}
}
