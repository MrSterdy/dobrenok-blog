<?php

namespace App\DTOs;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Category",
    type: "object",
    description: "Категория поста",
    properties: [
        new OA\Property(property: "id", type: "integer", description: "ID категории", example: 1),
        new OA\Property(property: "name", type: "string", description: "Название категории", example: "Технологии"),
        new OA\Property(property: "slug", type: "string", description: "URL-slug категории", example: "technologies"),
    ]
)]
readonly class CategoryDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
        ];
    }
}
