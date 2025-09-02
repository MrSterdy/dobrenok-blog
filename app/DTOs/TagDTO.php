<?php

namespace App\DTOs;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Tag",
    type: "object",
    description: "Тег поста",
    properties: [
        new OA\Property(property: "id", type: "integer", description: "ID тега", example: 1),
        new OA\Property(property: "name", type: "string", description: "Название тега", example: "Laravel"),
    ]
)]
readonly class TagDTO
{
    public function __construct(
        public int $id,
        public string $name,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
