<?php

namespace App\Commands;

readonly class DeactivateGoalsForProjectCommand
{
    public function __construct(
        public int $project_id,
    ) {}
}
