<?php

namespace App\Queries;

readonly class GetPostBySlugQuery
{
    public function __construct(
        public string $slug,
    ) {}
}

