<?php

namespace App\Queries;

readonly class GetPaymentByIdQuery
{
    public function __construct(
        public int $id,
    ) {}
}
