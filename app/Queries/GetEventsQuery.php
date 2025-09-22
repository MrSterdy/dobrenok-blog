<?php

namespace App\Queries;

readonly class GetEventsQuery
{
    public function __construct(
        public int $page = 1,
        public int $per_page = 15,
        public ?string $search = null,
        public ?int $project_id = null,
        public ?int $month = null,
        public ?string $sort_by = 'start_date',
        public string $sort_direction = 'desc',
    ) {}
}
