<?php

namespace App\Repositories\Contracts;

use App\Models\Subscription;
use App\Queries\GetSubscriptionsQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface SubscriptionRepositoryInterface
{
    public function getSubscriptions(GetSubscriptionsQuery $query): LengthAwarePaginator;

    public function getSubscriptionById(int $id): ?Subscription;

    public function create(array $attributes): Subscription;

    public function update(Subscription $subscription, array $attributes): bool;
}
