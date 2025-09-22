<?php

namespace App\Repositories\Contracts;

use App\Queries\GetEventsQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface EventRepositoryInterface
{
    public function getEvents(GetEventsQuery $query): LengthAwarePaginator;
}
