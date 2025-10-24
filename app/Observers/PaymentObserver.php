<?php

namespace App\Observers;

use App\Commands\AddPaymentToGoalCommand;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Services\ProjectService;
use Illuminate\Support\Facades\Log;

class PaymentObserver
{
    public function __construct(
        private readonly ProjectService $projectService
    ) {}

    /**
     * Handle the Payment "created" event.
     */
    public function created(Payment $payment): void
    {
        // При создании платежа ничего не делаем, ждем обновления статуса
    }

    /**
     * Handle the Payment "updated" event.
     */
    public function updated(Payment $payment): void
    {
        // Проверяем, изменился ли статус на успешный
        if ($payment->wasChanged('status') && $payment->status === PaymentStatus::COMPLETED) {
            $this->handleSuccessfulPayment($payment);
        }
    }

    /**
     * Обработка успешного платежа
     */
    private function handleSuccessfulPayment(Payment $payment): void
    {
        try {
            $command = new AddPaymentToGoalCommand(
                project_id: $payment->project_id,
                amount: (float) $payment->amount,
                currency: $payment->currency
            );

            $this->projectService->addPaymentToActiveGoal($command);

            Log::info('Payment successfully added to project goal', [
                'payment_id' => $payment->id,
                'project_id' => $payment->project_id,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to add payment to project goal', [
                'payment_id' => $payment->id,
                'project_id' => $payment->project_id,
                'error' => $e->getMessage(),
            ]);

            // В продакшене можно добавить уведомления администраторам
            // или поставить задачу в очередь для повторной обработки
        }
    }

    /**
     * Handle the Payment "deleted" event.
     */
    public function deleted(Payment $payment): void
    {
        //
    }

    /**
     * Handle the Payment "restored" event.
     */
    public function restored(Payment $payment): void
    {
        //
    }

    /**
     * Handle the Payment "force deleted" event.
     */
    public function forceDeleted(Payment $payment): void
    {
        //
    }
}
