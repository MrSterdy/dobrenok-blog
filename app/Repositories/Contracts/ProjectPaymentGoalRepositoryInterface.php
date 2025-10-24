<?php

namespace App\Repositories\Contracts;

use App\Commands\AddPaymentToPaymentGoalCommand;
use App\Commands\CreateProjectPaymentGoalCommand;
use App\Commands\DeactivateGoalsForProjectCommand;
use App\Models\ProjectPaymentGoal;
use App\Queries\GetActivePaymentGoalQuery;
use App\Queries\GetPaymentGoalByIdQuery;

interface ProjectPaymentGoalRepositoryInterface
{
    public function create(CreateProjectPaymentGoalCommand $command): ProjectPaymentGoal;

    public function findById(GetPaymentGoalByIdQuery $query): ?ProjectPaymentGoal;

    public function getActiveGoalForProject(GetActivePaymentGoalQuery $query): ?ProjectPaymentGoal;

    public function addPayment(AddPaymentToPaymentGoalCommand $command): void;

    public function deactivateGoalsForProject(DeactivateGoalsForProjectCommand $command): void;
}
