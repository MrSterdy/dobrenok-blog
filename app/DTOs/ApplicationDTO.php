<?php

namespace App\DTOs;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Application",
    type: "object",
    description: "Заявка",
    properties: [
        new OA\Property(property: "id", type: "integer", description: "ID заявки", example: 1),
        new OA\Property(property: "name", type: "string", description: "Название заявки", example: "Обратный звонок"),
        new OA\Property(property: "project_id", type: "integer", description: "ID проекта", example: 1),
        new OA\Property(property: "data", type: "object", description: "Данные заявки (имя, email, телефон и т.д.)"),
        new OA\Property(property: "created_at", type: "string", format: "date-time", description: "Дата создания"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", description: "Дата обновления"),
    ]
)]
readonly class ApplicationDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public int $project_id,
        public array $data,
        public string $created_at,
        public string $updated_at,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'project_id' => $this->project_id,
            'data' => $this->data,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
