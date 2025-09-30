<?php

namespace App\Services;

use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use App\Services\Payment\TBankWebhookStrategy;
use App\Services\Payment\WebhookStrategyInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    private array $strategies;

    public function __construct(
        private readonly PaymentRepositoryInterface $paymentRepository
    ) {
        $this->strategies = [
            PaymentProvider::TBANK->value => new TBankWebhookStrategy(),
        ];
    }

    public function processWebhook(PaymentProvider $provider, Request $request): bool
    {
        $strategy = $this->getStrategy($provider);

        // Проверяем подпись вебхука
        if (!$strategy->verifyWebhook($request)) {
            Log::error('Webhook signature verification failed', [
                'provider' => $provider->value,
                'request' => $request->all(),
            ]);
            return false;
        }

        // Парсим данные вебхука
        $webhookData = $strategy->parseWebhook($request);

        // Находим платеж по external_payment_id
        $payment = Payment::where('external_payment_id', $webhookData->external_payment_id)->first();

        if (!$payment) {
            Log::warning('Payment not found for webhook', [
                'provider' => $provider->value,
                'external_payment_id' => $webhookData->external_payment_id,
            ]);
            return false;
        }

        // Обновляем статус платежа
        $updateData = [
            'status' => PaymentStatus::from($webhookData->status),
        ];

        // Если платеж завершен или отменен, записываем время
        if (in_array($webhookData->status, ['completed', 'failed', 'cancelled'])) {
            $updateData['finished_at'] = now();
        }

        $updated = $this->paymentRepository->update($payment, $updateData);

        if ($updated) {
            Log::info('Payment status updated via webhook', [
                'payment_id' => $payment->id,
                'external_payment_id' => $webhookData->external_payment_id,
                'old_status' => $payment->status->value,
                'new_status' => $webhookData->status,
                'error_code' => $webhookData->error_code,
                'error_message' => $webhookData->error_message,
            ]);
        }

        return $updated;
    }

    private function getStrategy(PaymentProvider $provider): WebhookStrategyInterface
    {
        if (!isset($this->strategies[$provider->value])) {
            throw new \InvalidArgumentException("Webhook strategy for provider '{$provider->value}' not found");
        }

        return $this->strategies[$provider->value];
    }
}
