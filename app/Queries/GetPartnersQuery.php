<?php

namespace App\Queries;

readonly class GetPartnersQuery
{
    public function __construct(
        public int $page = 1,
        public int $per_page = 15,
        public ?string $search = null,
        public ?int $project_id = null,
        public ?string $sort_by = 'created_at',
        public string $sort_direction = 'desc',
    ) {}
}
