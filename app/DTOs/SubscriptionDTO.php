<?php

namespace App\DTOs;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Subscription",
    type: "object",
    description: "Подписка",
    properties: [
        new OA\Property(property: "id", type: "integer", description: "ID подписки", example: 1),
        new OA\Property(property: "email", type: "string", format: "email", description: "Email подписчика", example: "user@example.com"),
        new OA\Property(property: "name", type: "string", nullable: true, description: "Имя подписчика", example: "Иван Иванов"),
        new OA\Property(property: "amount", type: "number", format: "float", description: "Сумма подписки", example: 500.00),
        new OA\Property(property: "currency", type: "string", description: "Валюта", example: "RUB"),
        new OA\Property(property: "status", type: "string", description: "Статус подписки", example: "active"),
        new OA\Property(property: "external_subscription_id", type: "string", nullable: true, description: "Внешний ID подписки", example: "12345"),
        new OA\Property(property: "project_id", type: "integer", description: "ID проекта", example: 1),
        new OA\Property(property: "payment_url", type: "string", nullable: true, description: "URL для первого платежа", example: "https://securepay.tinkoff.ru/..."),
        new OA\Property(property: "next_billing_date", type: "string", format: "date-time", nullable: true, description: "Дата следующего платежа"),
        new OA\Property(property: "cancelled_at", type: "string", format: "date-time", nullable: true, description: "Дата отмены"),
        new OA\Property(property: "created_at", type: "string", format: "date-time", description: "Дата создания"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", description: "Дата обновления"),
    ]
)]
readonly class SubscriptionDTO
{
    public function __construct(
        public int $id,
        public string $email,
        public ?string $name,
        public float $amount,
        public string $currency,
        public string $status,
        public ?string $external_subscription_id,
        public int $project_id,
        public ?string $payment_url,
        public ?string $next_billing_date,
        public ?string $cancelled_at,
        public string $created_at,
        public string $updated_at,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'external_subscription_id' => $this->external_subscription_id,
            'project_id' => $this->project_id,
            'payment_url' => $this->payment_url,
            'next_billing_date' => $this->next_billing_date,
            'cancelled_at' => $this->cancelled_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
