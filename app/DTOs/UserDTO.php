<?php

namespace App\DTOs;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "User",
    type: "object",
    description: "Пользователь",
    properties: [
        new OA\Property(property: "id", type: "integer", description: "ID пользователя", example: 1),
        new OA\Property(property: "name", type: "string", description: "Имя пользователя", example: "John Doe"),
        new OA\Property(property: "email", type: "string", format: "email", description: "Email пользователя", example: "john@example.com"),
    ]
)]
readonly class UserDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
        ];
    }
}
