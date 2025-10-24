<?php

namespace App\Repositories;

use App\Commands\CreatePaymentCommand;
use App\Commands\UpdatePaymentCommand;
use App\Models\Payment;
use App\Queries\GetPaymentByIdQuery;
use App\Queries\GetPaymentsQuery;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PaymentRepository implements PaymentRepositoryInterface
{
    public function getPayments(GetPaymentsQuery $query): LengthAwarePaginator
    {
        $builder = Payment::query()->with('project');

        if ($query->search) {
            $builder->where(function ($q) use ($query) {
                $q->where('external_payment_id', 'like', "%{$query->search}%");
            });
        }

        if ($query->project_id) {
            $builder->where('project_id', $query->project_id);
        }

        if ($query->status) {
            $builder->where('status', $query->status);
        }

        if ($query->sort_by) {
            $builder->orderBy($query->sort_by, $query->sort_direction);
        }

        return $builder->paginate(
            perPage: $query->per_page,
            page: $query->page
        );
    }

    public function getPaymentById(GetPaymentByIdQuery $query): ?Payment
    {
        return Payment::query()->with('project')->find($query->id);
    }

    public function create(CreatePaymentCommand $command): Payment
    {
        return Payment::create([
            'external_payment_id' => $command->external_payment_id ?? null,
            'status' => $command->status ?? \App\Enums\PaymentStatus::PENDING,
            'amount' => $command->amount,
            'currency' => $command->currency,
            'project_id' => $command->project_id,
        ]);
    }

    public function update(UpdatePaymentCommand $command): ?Payment
    {
        $payment = Payment::find($command->payment_id);

        if (!$payment) {
            return null;
        }

        $payment->update($command->attributes);

        return $payment;
    }
}
