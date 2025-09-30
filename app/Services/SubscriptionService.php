<?php

namespace App\Services;

use App\Commands\CreatePaymentCommand;
use App\Commands\CreateSubscriptionCommand;
use App\DTOs\SubscriptionDTO;
use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Repositories\Contracts\SubscriptionRepositoryInterface;

class SubscriptionService
{
    public function __construct(
        private readonly PaymentService $paymentService,
        private readonly SubscriptionRepositoryInterface $subscriptionRepository
    ) {}

    public function createSubscription(CreateSubscriptionCommand $command): SubscriptionDTO
    {
        // Генерируем уникальный CustomerKey на основе email и project_id
        $customerKey = hash('sha256', $command->email . ':' . $command->project_id);

        // Создаем первый платеж через PaymentService с параметром Recurrent=Y
        $paymentCommand = new CreatePaymentCommand(
            project_id: $command->project_id,
            amount: $command->amount,
            currency: $command->currency,
            description: $command->description . ' (Подписка)',
            customer_email: $command->email,
            customer_phone: null,
            customer_key: $customerKey, // Обязателен для Recurrent=Y
            success_url: $command->success_url,
            fail_url: $command->fail_url,
            payment_provider: $command->payment_provider,
            is_recurrent: true, // Включаем рекуррентные платежи
        );

        // Создаем инициирующий платеж для подписки
        $paymentResult = $this->paymentService->createPayment($paymentCommand);

        // Сохраняем подписку в базе данных через репозиторий
        $subscription = $this->subscriptionRepository->create([
            'email' => $command->email,
            'name' => $command->name,
            'customer_key' => $customerKey,
            'amount' => $command->amount,
            'currency' => $command->currency,
            'status' => SubscriptionStatus::ACTIVE,
            'external_subscription_id' => $paymentResult->external_payment_id, // Будет обновлено после оплаты
            'rebill_id' => null, // Будет получен из вебхука после первой оплаты
            'project_id' => $command->project_id,
            'next_billing_date' => now()->addMonth(), // Следующий платеж через месяц
        ]);

        return $this->mapToDTO($subscription, $paymentResult->payment_url);
    }

    public function processRecurringPayment(Subscription $subscription): void
    {
        if (!$subscription->rebill_id) {
            throw new \InvalidArgumentException("Subscription #{$subscription->id} has no RebillId");
        }

        if (!$subscription->isActive()) {
            throw new \InvalidArgumentException("Subscription #{$subscription->id} is not active");
        }

        // Создаем автоплатеж через RebillId
        $paymentCommand = new CreatePaymentCommand(
            project_id: $subscription->project_id,
            amount: (float) $subscription->amount,
            currency: $subscription->currency,
            description: "Автоплатеж по подписке #{$subscription->id}",
            customer_email: $subscription->email,
            customer_phone: null,
            customer_key: $subscription->customer_key,
            success_url: null,
            fail_url: null,
            payment_provider: \App\Enums\PaymentProvider::TBANK,
            is_recurrent: false,
            rebill_id: $subscription->rebill_id,
        );

        try {
            $paymentResult = $this->paymentService->createPayment($paymentCommand);

            // Обновляем дату следующего платежа
            $this->subscriptionRepository->update($subscription, [
                'next_billing_date' => now()->addMonth(),
            ]);

            \Illuminate\Support\Facades\Log::info('Recurring payment processed', [
                'subscription_id' => $subscription->id,
                'payment_id' => $paymentResult->id,
                'amount' => $subscription->amount,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Recurring payment failed', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function mapToDTO(Subscription $subscription, ?string $paymentUrl = null): SubscriptionDTO
    {
        return new SubscriptionDTO(
            id: $subscription->id,
            email: $subscription->email,
            name: $subscription->name,
            amount: (float) $subscription->amount,
            currency: $subscription->currency,
            status: $subscription->status->value,
            external_subscription_id: $subscription->external_subscription_id,
            project_id: $subscription->project_id,
            payment_url: $paymentUrl,
            next_billing_date: $subscription->next_billing_date?->toISOString(),
            cancelled_at: $subscription->cancelled_at?->toISOString(),
            created_at: $subscription->created_at->toISOString(),
            updated_at: $subscription->updated_at->toISOString(),
        );
    }
}
