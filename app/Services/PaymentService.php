<?php

namespace App\Services;

use App\Commands\CreatePaymentCommand;
use App\DTOs\PaymentDTO;
use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use App\Services\Payment\PaymentStrategyInterface;
use App\Services\Payment\TBankPaymentStrategy;

class PaymentService
{
    private array $strategies;

    public function __construct(
        private readonly PaymentRepositoryInterface $paymentRepository
    ) {
        $this->strategies = [
            PaymentProvider::TBANK->value => new TBankPaymentStrategy(),
        ];
    }

    public function createPayment(CreatePaymentCommand $command): PaymentDTO
    {
        $strategy = $this->getStrategy($command->payment_provider);

        // Создаем платеж через выбранную стратегию
        $paymentResult = $strategy->createPayment($command);

        // Сохраняем платеж в базе данных через репозиторий
        $payment = $this->paymentRepository->create([
            'external_payment_id' => $paymentResult->external_payment_id,
            'status' => PaymentStatus::from($paymentResult->status),
            'amount' => $command->amount,
            'currency' => $command->currency,
            'project_id' => $command->project_id,
        ]);

        return $this->mapToDTO($payment, $paymentResult->payment_url);
    }

    private function getStrategy(PaymentProvider $provider): PaymentStrategyInterface
    {
        if (!isset($this->strategies[$provider->value])) {
            throw new \InvalidArgumentException("Payment provider '{$provider->value}' not supported");
        }

        return $this->strategies[$provider->value];
    }

    private function mapToDTO(Payment $payment, ?string $paymentUrl = null): PaymentDTO
    {
        return new PaymentDTO(
            id: $payment->id,
            external_payment_id: $payment->external_payment_id,
            status: $payment->status->value,
            amount: (float) $payment->amount,
            currency: $payment->currency,
            project_id: $payment->project_id,
            payment_url: $paymentUrl,
            finished_at: $payment->finished_at?->toISOString(),
            created_at: $payment->created_at->toISOString(),
            updated_at: $payment->updated_at->toISOString(),
        );
    }
}
