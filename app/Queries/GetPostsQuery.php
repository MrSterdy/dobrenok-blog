<?php

namespace App\Queries;

readonly class GetPostsQuery
{
    public function __construct(
        public int $page = 1,
        public int $per_page = 15,
        public ?string $search = null,
        public ?int $project_id = null,
        public ?int $category_id = null,
        public ?int $tag_id = null,
        public string $status = 'published',
        public ?string $sort_by = 'published_at',
        public string $sort_direction = 'desc',
    ) {}
}

