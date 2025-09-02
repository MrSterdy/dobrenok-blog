<?php

namespace App\Repositories\Contracts;

use App\Models\Project;
use App\Queries\GetProjectByIdQuery;
use App\Queries\GetProjectsQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProjectRepositoryInterface
{
    public function getProjects(GetProjectsQuery $query): LengthAwarePaginator;

    public function getProjectById(GetProjectByIdQuery $query): ?Project;
}

