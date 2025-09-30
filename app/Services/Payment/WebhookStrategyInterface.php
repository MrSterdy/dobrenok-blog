<?php

namespace App\Services\Payment;

use App\DTOs\PaymentWebhookDTO;
use Illuminate\Http\Request;

interface WebhookStrategyInterface
{
    /**
     * Проверяет подпись вебхука
     */
    public function verifyWebhook(Request $request): bool;

    /**
     * Парсит данные вебхука в DTO
     */
    public function parseWebhook(Request $request): PaymentWebhookDTO;
}
