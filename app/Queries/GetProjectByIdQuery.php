<?php

namespace App\Queries;

readonly class GetProjectByIdQuery
{
    public function __construct(
        public int $id,
    ) {}
}

