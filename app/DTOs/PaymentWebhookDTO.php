<?php

namespace App\DTOs;

readonly class PaymentWebhookDTO
{
    public function __construct(
        public string $external_payment_id,
        public string $status,
        public ?string $error_code = null,
        public ?string $error_message = null,
        public ?array $additional_data = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            external_payment_id: $data['external_payment_id'],
            status: $data['status'],
            error_code: $data['error_code'] ?? null,
            error_message: $data['error_message'] ?? null,
            additional_data: $data['additional_data'] ?? null,
        );
    }
}
