<?php

namespace App\Repositories\Contracts;

use App\Commands\CreateApplicationCommand;
use App\Queries\GetApplicationsQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ApplicationRepositoryInterface
{
    public function getApplications(GetApplicationsQuery $query): LengthAwarePaginator;

    public function create(CreateApplicationCommand $command): int;
}
