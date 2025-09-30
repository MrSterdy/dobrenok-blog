<?php

namespace App\Repositories\Contracts;

use App\Models\Payment;
use App\Queries\GetPaymentsQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PaymentRepositoryInterface
{
    public function getPayments(GetPaymentsQuery $query): LengthAwarePaginator;

    public function getPaymentById(int $id): ?Payment;

    public function create(array $attributes): Payment;

    public function update(Payment $payment, array $attributes): bool;
}
