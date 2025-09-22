<?php

namespace App\DTOs;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Project",
    type: "object",
    description: "Проект",
    properties: [
        new OA\Property(property: "id", type: "integer", description: "ID проекта", example: 1),
        new OA\Property(property: "name", type: "string", description: "Название проекта", example: "Laravel Blog"),
        new OA\Property(property: "description", type: "string", nullable: true, description: "Описание проекта", example: "A blog project using Laravel"),
        new OA\Property(property: "cover_photo_url", type: "string", nullable: true, description: "URL обложки проекта", example: "http://localhost:8000/storage/project-images/cover.jpg"),
        new OA\Property(property: "home_url", type: "string", nullable: true, description: "URL домашней страницы проекта", example: "http://localhost:8000/projects/1"),
        new OA\Property(property: "posts_count", type: "integer", description: "Количество постов в проекте", example: 5),
        new OA\Property(property: "created_at", type: "string", format: "date-time", description: "Дата создания", example: "2024-01-15T10:30:00Z"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", description: "Дата обновления", example: "2024-01-15T10:30:00Z"),
    ]
)]
readonly class ProjectDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
        public ?string $cover_photo_url,
        public ?string $home_url,
        public int $posts_count,
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
            'home_url' => $this->home_url,
            'posts_count' => $this->posts_count,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
