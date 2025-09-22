<?php

namespace App\Repositories;

use App\Commands\CreateApplicationCommand;
use App\Models\Application;
use App\Queries\GetApplicationsQuery;
use App\Repositories\Contracts\ApplicationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ApplicationRepository implements ApplicationRepositoryInterface
{
    public function getApplications(GetApplicationsQuery $query): LengthAwarePaginator
    {
        $builder = Application::query()->with('project');

        if ($query->search) {
            $builder->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query->search}%");
            });
        }

        if ($query->project_id) {
            $builder->where('project_id', $query->project_id);
        }

        if ($query->sort_by) {
            $builder->orderBy($query->sort_by, $query->sort_direction);
        }

        return $builder->paginate(
            perPage: $query->per_page,
            page: $query->page
        );
    }

    public function create(CreateApplicationCommand $command): int
    {
        $application = Application::query()->create([
            'name' => $command->name,
            'data' => $command->data,
            'project_id' => $command->project_id,
        ]);
        return (int) $application->id;
    }
}
