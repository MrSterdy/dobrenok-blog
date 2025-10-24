<?php

namespace App\Repositories;

use App\Models\Project;
use App\Queries\GetProjectByIdQuery;
use App\Queries\GetProjectsQuery;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProjectRepository implements ProjectRepositoryInterface
{
    public function getProjects(GetProjectsQuery $query): LengthAwarePaginator
    {
        $builder = Project::query()
            ->withCount('posts')
            ->with('activePaymentGoal');

        if ($query->search) {
            $builder->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query->search}%")
                    ->orWhere('description', 'like', "%{$query->search}%");
            });
        }

        if ($query->sort_by) {
            $builder->orderBy($query->sort_by, $query->sort_direction);
        }

        return $builder->paginate(
            perPage: $query->per_page,
            page: $query->page
        );
    }

    public function getProjectById(GetProjectByIdQuery $query): ?Project
    {
        return Project::query()
            ->withCount('posts')
            ->with('activePaymentGoal')
            ->find($query->id);
    }
}
