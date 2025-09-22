<?php

namespace App\Services;

use App\DTOs\ApplicationDTO;
use App\DTOs\PaginatedResponseDTO;
use App\Commands\CreateApplicationCommand;
use App\Models\Application;
use App\Queries\GetApplicationsQuery;
use App\Repositories\Contracts\ApplicationRepositoryInterface;

class ApplicationService
{
    public function __construct(
        private readonly ApplicationRepositoryInterface $applicationRepository
    ) {}

    public function getApplications(GetApplicationsQuery $query): PaginatedResponseDTO
    {
        $apps = $this->applicationRepository->getApplications($query);

        $dtos = $apps->items();
        $dtos = array_map(function (Application $app) {
            return $this->mapToDTO($app);
        }, $dtos);

        return new PaginatedResponseDTO(
            data: $dtos,
            current_page: $apps->currentPage(),
            last_page: $apps->lastPage(),
            per_page: $apps->perPage(),
            total: $apps->total(),
            next_page_url: $apps->nextPageUrl(),
            prev_page_url: $apps->previousPageUrl(),
        );
    }

    public function create(CreateApplicationCommand $command): ApplicationDTO
    {
        $id = $this->applicationRepository->create($command);
        $app = Application::query()->findOrFail($id);
        return $this->mapToDTO($app);
    }

    private function mapToDTO(Application $app): ApplicationDTO
    {
        return new ApplicationDTO(
            id: $app->id,
            name: $app->name,
            project_id: $app->project_id,
            data: $app->data ?? [],
            created_at: $app->created_at->toISOString(),
            updated_at: $app->updated_at->toISOString(),
        );
    }
}
