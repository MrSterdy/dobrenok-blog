<?php

namespace App\Queries;

readonly class GetSubscriptionsQuery
{
    public function __construct(
        public int $page = 1,
        public int $per_page = 15,
        public ?string $search = null,
        public ?int $project_id = null,
        public ?string $status = null,
        public ?string $sort_by = 'created_at',
        public string $sort_direction = 'desc',
    ) {}
}
