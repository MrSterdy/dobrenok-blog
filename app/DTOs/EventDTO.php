<?php

namespace App\DTOs;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Event",
    type: "object",
    description: "Мероприятие проекта",
    properties: [
        new OA\Property(property: "id", type: "integer", description: "ID мероприятия", example: 1),
        new OA\Property(property: "name", type: "string", description: "Название", example: "Митап Laravel"),
        new OA\Property(property: "slug", type: "string", description: "Ссылка", example: "mitap-laravel"),
        new OA\Property(property: "short_description", type: "string", description: "Краткое описание", example: "Краткое описание мероприятия..."),
        new OA\Property(property: "body", type: "string", description: "Описание мероприятия", example: "Подробности мероприятия..."),
        new OA\Property(property: "cover_photo_url", type: "string", description: "URL обложки", example: "http://localhost:8000/storage/event-images/cover.jpg"),
        new OA\Property(property: "start_date", type: "string", format: "date-time", description: "Дата начала", example: "2025-09-20T18:00:00Z"),
        new OA\Property(property: "created_at", type: "string", format: "date-time", description: "Дата создания", example: "2025-09-19T10:30:00Z"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", description: "Дата обновления", example: "2025-09-19T10:30:00Z"),
    ]
)]
readonly class EventDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public string $short_description,
        public string $body,
        public string $cover_photo_url,
        public string $start_date,
        public string $created_at,
        public string $updated_at,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'body' => $this->body,
            'cover_photo_url' => $this->cover_photo_url,
            'start_date' => $this->start_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
