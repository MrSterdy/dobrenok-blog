<?php

namespace App\Services\Payment;

use App\Commands\CreatePaymentCommand;
use App\DTOs\PaymentCreationResultDTO;

interface PaymentStrategyInterface
{
    public function createPayment(CreatePaymentCommand $command): PaymentCreationResultDTO;
}
