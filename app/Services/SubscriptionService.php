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
        // Создаем первый платеж через PaymentService с параметром Recurrent=Y
        $paymentCommand = new CreatePaymentCommand(
            project_id: $command->project_id,
            amount: $command->amount,
            currency: $command->currency,
            description: $command->description . ' (Подписка)',
            customer_email: $command->email,
            customer_phone: null,
            success_url: $command->success_url,
            fail_url: $command->fail_url,
            payment_provider: $command->payment_provider,
        );

        // Создаем инициирующий платеж для подписки
        $paymentResult = $this->paymentService->createPayment($paymentCommand);

        // Сохраняем подписку в базе данных через репозиторий
        $subscription = $this->subscriptionRepository->create([
            'email' => $command->email,
            'name' => $command->name,
            'amount' => $command->amount,
            'currency' => $command->currency,
            'status' => SubscriptionStatus::ACTIVE,
            'external_subscription_id' => $paymentResult->external_payment_id, // Будет обновлено после оплаты
            'project_id' => $command->project_id,
            'next_billing_date' => now()->addMonth(), // Следующий платеж через месяц
        ]);

        return $this->mapToDTO($subscription, $paymentResult->payment_url);
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
