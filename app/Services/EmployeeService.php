<?php

namespace App\Services;

use App\DTOs\EmployeeDTO;
use App\DTOs\PaginatedResponseDTO;
use App\Models\Employee;
use App\Queries\GetEmployeesQuery;
use App\Repositories\Contracts\EmployeeRepositoryInterface;

class EmployeeService
{
    public function __construct(
        private readonly EmployeeRepositoryInterface $employeeRepository
    ) {}

    public function getEmployees(GetEmployeesQuery $query): PaginatedResponseDTO
    {
        $employees = $this->employeeRepository->getEmployees($query);

        $employeeDTOs = $employees->items();
        $employeeDTOs = array_map(function (Employee $employee) {
            return $this->mapEmployeeToDTO($employee);
        }, $employeeDTOs);

        return new PaginatedResponseDTO(
            data: $employeeDTOs,
            current_page: $employees->currentPage(),
            last_page: $employees->lastPage(),
            per_page: $employees->perPage(),
            total: $employees->total(),
            next_page_url: $employees->nextPageUrl(),
            prev_page_url: $employees->previousPageUrl(),
        );
    }

    private function mapEmployeeToDTO(Employee $employee): EmployeeDTO
    {
        return new EmployeeDTO(
            id: $employee->id,
            name: $employee->name,
            description: $employee->description,
            group: $employee->group,
            cover_photo_url: $employee->cover_photo_path ? asset('storage/' . $employee->cover_photo_path) : '',
            created_at: $employee->created_at->toISOString(),
            updated_at: $employee->updated_at->toISOString(),
        );
    }
}
