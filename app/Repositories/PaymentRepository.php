<?php

namespace App\Repositories;

use App\Models\Payment;
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

    public function getPaymentById(int $id): ?Payment
    {
        return Payment::query()->with('project')->find($id);
    }

    public function create(array $attributes): Payment
    {
        return Payment::query()->create($attributes);
    }

    public function update(Payment $payment, array $attributes): bool
    {
        return $payment->update($attributes);
    }
}
