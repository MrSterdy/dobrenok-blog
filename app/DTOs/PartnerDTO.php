<?php

namespace App\DTOs;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Partner",
    type: "object",
    description: "Партнер проекта",
    properties: [
        new OA\Property(property: "id", type: "integer", description: "ID партнера", example: 1),
        new OA\Property(property: "name", type: "string", description: "Название партнера", example: "Acme Inc."),
        new OA\Property(property: "description", type: "string", nullable: true, description: "Описание партнера", example: "Описание партнера"),
        new OA\Property(property: "cover_photo_url", type: "string", description: "URL изображения", example: "http://localhost:8000/storage/partner-images/logo.png"),
        new OA\Property(property: "created_at", type: "string", format: "date-time", description: "Дата создания", example: "2024-01-15T10:30:00Z"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", description: "Дата обновления", example: "2024-01-15T10:30:00Z"),
    ]
)]
readonly class PartnerDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
        public string $cover_photo_url,
        public string $created_at,
        public string $updated_at,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'cover_photo_url' => $this->cover_photo_url,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
