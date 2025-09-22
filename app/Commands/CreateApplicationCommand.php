<?php

namespace App\Commands;

class CreateApplicationCommand
{
    public function __construct(
        public int $project_id,
        public string $name,
        public array $data,
    ) {}
}
