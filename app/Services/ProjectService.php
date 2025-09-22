<?php

namespace App\Services;

use App\DTOs\PaginatedResponseDTO;
use App\DTOs\ProjectDTO;
use App\Models\Project;
use App\Queries\GetProjectByIdQuery;
use App\Queries\GetProjectsQuery;
use App\Repositories\Contracts\ProjectRepositoryInterface;

class ProjectService
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository
    ) {}

    public function getProjects(GetProjectsQuery $query): PaginatedResponseDTO
    {
        $projects = $this->projectRepository->getProjects($query);

        $projectDTOs = $projects->items();
        $projectDTOs = array_map(function (Project $project) {
            return $this->mapProjectToDTO($project);
        }, $projectDTOs);

        return new PaginatedResponseDTO(
            data: $projectDTOs,
            current_page: $projects->currentPage(),
            last_page: $projects->lastPage(),
            per_page: $projects->perPage(),
            total: $projects->total(),
            next_page_url: $projects->nextPageUrl(),
            prev_page_url: $projects->previousPageUrl(),
        );
    }

    public function getProjectById(GetProjectByIdQuery $query): ?ProjectDTO
    {
        $project = $this->projectRepository->getProjectById($query);

        return $project ? $this->mapProjectToDTO($project) : null;
    }

    private function mapProjectToDTO(Project $project): ProjectDTO
    {
        return new ProjectDTO(
            id: $project->id,
            name: $project->name,
            description: $project->description,
            cover_photo_url: $project->cover_photo_path ? asset('storage/' . $project->cover_photo_path) : '',
            home_url: $project->home_url,
            posts_count: $project->posts_count ?? 0,
            created_at: $project->created_at->toISOString(),
            updated_at: $project->updated_at->toISOString(),
        );
    }
}
