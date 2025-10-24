<?php

namespace App\Repositories\Contracts;

use App\Commands\CreatePaymentCommand;
use App\Commands\UpdatePaymentCommand;
use App\Models\Payment;
use App\Queries\GetPaymentByIdQuery;
use App\Queries\GetPaymentsQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PaymentRepositoryInterface
{
    public function getPayments(GetPaymentsQuery $query): LengthAwarePaginator;

    public function getPaymentById(GetPaymentByIdQuery $query): ?Payment;

    public function create(CreatePaymentCommand $command): Payment;

    public function update(UpdatePaymentCommand $command): ?Payment;
}
