<?php

namespace App\Repositories\Contracts;

use App\Queries\GetEmployeesQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface EmployeeRepositoryInterface
{
    public function getEmployees(GetEmployeesQuery $query): LengthAwarePaginator;
}
