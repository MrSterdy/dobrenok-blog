<?php

namespace App\Services;

use App\Commands\AddPaymentToGoalCommand;
use App\Commands\CreateProjectPaymentGoalCommand;
use App\DTOs\PaginatedResponseDTO;
use App\DTOs\ProjectDTO;
use App\DTOs\ProjectPaymentGoalDTO;
use App\Models\Project;
use App\Models\ProjectPaymentGoal;
use App\Queries\GetActivePaymentGoalQuery;
use App\Queries\GetProjectByIdQuery;
use App\Queries\GetProjectsQuery;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use App\Repositories\Contracts\ProjectPaymentGoalRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectService
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly ProjectPaymentGoalRepositoryInterface $paymentGoalRepository
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
            cover_photo_url: $project->cover_photo_url ? asset('storage/' . $project->cover_photo_url) : null,
            home_url: $project->home_url,
            posts_count: $project->posts_count ?? 0,
            payment_goal: $project->activePaymentGoal ? $this->mapPaymentGoalToDTO($project->activePaymentGoal) : null,
            created_at: $project->created_at->toISOString(),
            updated_at: $project->updated_at->toISOString(),
        );
    }

    /**
     * Добавить платеж к активной цели проекта
     */
    public function addPaymentToActiveGoal(AddPaymentToGoalCommand $command): void
    {
        DB::transaction(function () use ($command) {
            $project = $this->projectRepository->getProjectById(
                new GetProjectByIdQuery($command->project_id)
            );

            if (!$project) {
                throw new ModelNotFoundException("Project with ID {$command->project_id} not found");
            }

            $activeGoal = $project->activePaymentGoal;

            if (!$activeGoal) {
                Log::warning('No active payment goal found for project', [
                    'project_id' => $command->project_id,
                    'payment_amount' => $command->amount,
                    'currency' => $command->currency,
                ]);
                return;
            }

            // Проверяем совпадение валют
            if ($activeGoal->currency !== $command->currency) {
                Log::warning('Currency mismatch between payment and goal', [
                    'project_id' => $command->project_id,
                    'goal_currency' => $activeGoal->currency,
                    'payment_currency' => $command->currency,
                    'amount' => $command->amount,
                ]);

                // В будущем здесь можно добавить конвертацию валют
                // Пока просто логируем и не добавляем к цели
                return;
            }

            // Добавляем сумму к цели
            $addPaymentCommand = new \App\Commands\AddPaymentToPaymentGoalCommand(
                goal_id: $activeGoal->id,
                amount: $command->amount
            );
            $this->paymentGoalRepository->addPayment($addPaymentCommand);

            Log::info('Payment amount added to project goal', [
                'project_id' => $command->project_id,
                'goal_id' => $activeGoal->id,
                'amount_added' => $command->amount,
                'new_current_amount' => $activeGoal->current_amount + $command->amount,
                'target_amount' => $activeGoal->target_amount,
            ]);

            // Проверяем, достигнута ли цель
            $updatedGoal = $this->paymentGoalRepository->findById(
                new \App\Queries\GetPaymentGoalByIdQuery($activeGoal->id)
            );
            if ($updatedGoal && $updatedGoal->isGoalReached()) {
                Log::info('Project payment goal reached!', [
                    'project_id' => $command->project_id,
                    'goal_id' => $activeGoal->id,
                    'final_amount' => $updatedGoal->current_amount,
                    'target_amount' => $updatedGoal->target_amount,
                ]);

                // Здесь можно добавить уведомления, события и т.д.
                // event(new PaymentGoalReachedEvent($updatedGoal));
            }
        });
    }

    /**
     * Создать новую цель сбора средств для проекта
     */
    public function createPaymentGoal(CreateProjectPaymentGoalCommand $command): ProjectPaymentGoalDTO
    {
        // Деактивируем предыдущие цели
        $deactivateCommand = new \App\Commands\DeactivateGoalsForProjectCommand($command->project_id);
        $this->paymentGoalRepository->deactivateGoalsForProject($deactivateCommand);

        $paymentGoal = $this->paymentGoalRepository->create($command);

        return $this->mapPaymentGoalToDTO($paymentGoal);
    }

    /**
     * Получить активную цель проекта
     */
    public function getActiveGoalForProject(GetActivePaymentGoalQuery $query): ?ProjectPaymentGoalDTO
    {
        $goal = $this->paymentGoalRepository->getActiveGoalForProject($query);

        return $goal ? $this->mapPaymentGoalToDTO($goal) : null;
    }

    private function mapPaymentGoalToDTO(ProjectPaymentGoal $paymentGoal): ProjectPaymentGoalDTO
    {
        return new ProjectPaymentGoalDTO(
            id: $paymentGoal->id,
            target_amount: (float) $paymentGoal->target_amount,
            current_amount: (float) $paymentGoal->current_amount,
            currency: $paymentGoal->currency,
            description: $paymentGoal->description,
            deadline: $paymentGoal->deadline?->format('Y-m-d'),
            progress_percentage: $paymentGoal->progress_percentage,
            remaining_amount: $paymentGoal->remaining_amount,
            is_goal_reached: $paymentGoal->isGoalReached(),
            is_active: $paymentGoal->is_active,
            created_at: $paymentGoal->created_at->toISOString(),
            updated_at: $paymentGoal->updated_at->toISOString(),
        );
    }
}
