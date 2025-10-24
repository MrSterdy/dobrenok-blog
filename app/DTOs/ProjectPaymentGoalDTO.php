<?php

namespace App\DTOs;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "ProjectPaymentGoal",
    type: "object",
    description: "Цель сбора средств для проекта",
    properties: [
        new OA\Property(property: "id", type: "integer", description: "ID цели", example: 1),
        new OA\Property(property: "target_amount", type: "number", format: "float", description: "Целевая сумма", example: 10000.00),
        new OA\Property(property: "current_amount", type: "number", format: "float", description: "Текущая накопленная сумма", example: 2500.00),
        new OA\Property(property: "currency", type: "string", description: "Валюта", example: "RUB"),
        new OA\Property(property: "description", type: "string", nullable: true, description: "Описание цели", example: "Сбор средств на разработку новой функции"),
        new OA\Property(property: "deadline", type: "string", format: "date", nullable: true, description: "Крайний срок", example: "2024-12-31"),
        new OA\Property(property: "progress_percentage", type: "number", format: "float", description: "Процент выполнения", example: 25.0),
        new OA\Property(property: "remaining_amount", type: "number", format: "float", description: "Оставшаяся сумма", example: 7500.00),
        new OA\Property(property: "is_goal_reached", type: "boolean", description: "Достигнута ли цель", example: false),
        new OA\Property(property: "is_active", type: "boolean", description: "Активна ли цель", example: true),
        new OA\Property(property: "created_at", type: "string", format: "date-time", description: "Дата создания", example: "2024-01-15T10:30:00Z"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", description: "Дата обновления", example: "2024-01-15T10:30:00Z"),
    ]
)]
readonly class ProjectPaymentGoalDTO
{
    public function __construct(
        public int $id,
        public float $target_amount,
        public float $current_amount,
        public string $currency,
        public ?string $description,
        public ?string $deadline,
        public float $progress_percentage,
        public float $remaining_amount,
        public bool $is_goal_reached,
        public bool $is_active,
        public string $created_at,
        public string $updated_at,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'target_amount' => $this->target_amount,
            'current_amount' => $this->current_amount,
            'currency' => $this->currency,
            'description' => $this->description,
            'deadline' => $this->deadline,
            'progress_percentage' => $this->progress_percentage,
            'remaining_amount' => $this->remaining_amount,
            'is_goal_reached' => $this->is_goal_reached,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
