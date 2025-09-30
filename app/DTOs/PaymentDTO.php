<?php

namespace App\DTOs;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Payment",
    type: "object",
    description: "Платеж",
    properties: [
        new OA\Property(property: "id", type: "integer", description: "ID платежа", example: 1),
        new OA\Property(property: "external_payment_id", type: "string", description: "Внешний ID платежа", example: "12345678"),
        new OA\Property(property: "status", type: "string", description: "Статус платежа", example: "pending"),
        new OA\Property(property: "amount", type: "number", format: "float", description: "Сумма платежа", example: 1000.50),
        new OA\Property(property: "currency", type: "string", description: "Валюта", example: "RUB"),
        new OA\Property(property: "project_id", type: "integer", description: "ID проекта", example: 1),
        new OA\Property(property: "payment_url", type: "string", nullable: true, description: "URL для оплаты", example: "https://securepay.tinkoff.ru/..."),
        new OA\Property(property: "finished_at", type: "string", format: "date-time", nullable: true, description: "Дата завершения"),
        new OA\Property(property: "created_at", type: "string", format: "date-time", description: "Дата создания"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", description: "Дата обновления"),
    ]
)]
readonly class PaymentDTO
{
    public function __construct(
        public int $id,
        public string $external_payment_id,
        public string $status,
        public float $amount,
        public string $currency,
        public int $project_id,
        public ?string $payment_url,
        public ?string $finished_at,
        public string $created_at,
        public string $updated_at,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'external_payment_id' => $this->external_payment_id,
            'status' => $this->status,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'project_id' => $this->project_id,
            'payment_url' => $this->payment_url,
            'finished_at' => $this->finished_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
