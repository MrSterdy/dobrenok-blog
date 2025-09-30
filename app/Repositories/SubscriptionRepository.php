<?php

namespace App\Repositories;

use App\Models\Subscription;
use App\Queries\GetSubscriptionsQuery;
use App\Repositories\Contracts\SubscriptionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SubscriptionRepository implements SubscriptionRepositoryInterface
{
    public function getSubscriptions(GetSubscriptionsQuery $query): LengthAwarePaginator
    {
        $builder = Subscription::query()->with('project');

        if ($query->search) {
            $builder->where(function ($q) use ($query) {
                $q->where('email', 'like', "%{$query->search}%")
                    ->orWhere('name', 'like', "%{$query->search}%");
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

    public function getSubscriptionById(int $id): ?Subscription
    {
        return Subscription::query()->with('project')->find($id);
    }

    public function create(array $attributes): Subscription
    {
        return Subscription::query()->create($attributes);
    }

    public function update(Subscription $subscription, array $attributes): bool
    {
        return $subscription->update($attributes);
    }
}
