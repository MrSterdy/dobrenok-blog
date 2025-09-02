<?php

namespace App\DTOs;

readonly class PaginatedResponseDTO
{
    public function __construct(
        public array $data,
        public int $current_page,
        public int $last_page,
        public int $per_page,
        public int $total,
        public ?string $next_page_url,
        public ?string $prev_page_url,
    ) {}

    public function toArray(): array
    {
        return [
            'data' => array_map(
                fn($item) => method_exists($item, 'toArray') ? $item->toArray() : $item,
                $this->data
            ),
            'pagination' => [
                'current_page' => $this->current_page,
                'last_page' => $this->last_page,
                'per_page' => $this->per_page,
                'total' => $this->total,
                'next_page_url' => $this->next_page_url,
                'prev_page_url' => $this->prev_page_url,
            ],
        ];
    }
}
