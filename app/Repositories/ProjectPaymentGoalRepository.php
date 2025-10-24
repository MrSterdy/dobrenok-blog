<?php

namespace App\Repositories;

use App\Commands\AddPaymentToPaymentGoalCommand;
use App\Commands\CreateProjectPaymentGoalCommand;
use App\Commands\DeactivateGoalsForProjectCommand;
use App\Models\ProjectPaymentGoal;
use App\Queries\GetActivePaymentGoalQuery;
use App\Queries\GetPaymentGoalByIdQuery;
use App\Repositories\Contracts\ProjectPaymentGoalRepositoryInterface;

class ProjectPaymentGoalRepository implements ProjectPaymentGoalRepositoryInterface
{
    public function create(CreateProjectPaymentGoalCommand $command): ProjectPaymentGoal
    {
        return ProjectPaymentGoal::create([
            'project_id' => $command->project_id,
            'target_amount' => $command->target_amount,
            'current_amount' => 0,
            'currency' => $command->currency,
            'description' => $command->description,
            'deadline' => $command->deadline,
            'is_active' => true,
        ]);
    }

    public function findById(GetPaymentGoalByIdQuery $query): ?ProjectPaymentGoal
    {
        return ProjectPaymentGoal::find($query->id);
    }

    public function getActiveGoalForProject(GetActivePaymentGoalQuery $query): ?ProjectPaymentGoal
    {
        return ProjectPaymentGoal::where('project_id', $query->project_id)
            ->where('is_active', true)
            ->first();
    }

    public function addPayment(AddPaymentToPaymentGoalCommand $command): void
    {
        ProjectPaymentGoal::where('id', $command->goal_id)
            ->increment('current_amount', $command->amount);
    }

    public function deactivateGoalsForProject(DeactivateGoalsForProjectCommand $command): void
    {
        ProjectPaymentGoal::where('project_id', $command->project_id)
            ->where('is_active', true)
            ->update(['is_active' => false]);
    }
}
