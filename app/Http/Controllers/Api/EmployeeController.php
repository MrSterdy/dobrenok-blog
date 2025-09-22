<?php

namespace App\Http\Controllers\Api;

use App\Constants\ApiConstants;
use App\Http\Controllers\Controller;
use App\Http\Responses\ErrorResponse;
use App\Http\Responses\SuccessResponse;
use App\Queries\GetEmployeesQuery;
use App\Services\EmployeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Employees", description: "Сотрудники проектов")]
class EmployeeController extends Controller
{
    public function __construct(
        private readonly EmployeeService $employeeService
    ) {}

    #[OA\Get(
        path: "/projects/{id}/employees",
        summary: "Список сотрудников проекта",
        description: "Возвращает пагинированный список сотрудников для указанного проекта",
        tags: ["Employees"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID проекта",
                schema: new OA\Schema(type: "integer", example: 1)
            ),
            new OA\Parameter(
                name: "page",
                in: "query",
                description: "Номер страницы",
                schema: new OA\Schema(type: "integer", minimum: 1, example: 1)
            ),
            new OA\Parameter(
                name: "per_page",
                in: "query",
                description: "Количество элементов на странице",
                schema: new OA\Schema(type: "integer", minimum: 1, maximum: 50, example: 15)
            ),
            new OA\Parameter(
                name: "search",
                in: "query",
                description: "Поиск по имени или описанию",
                schema: new OA\Schema(type: "string", example: "Иван")
            ),
            new OA\Parameter(
                name: "sort_by",
                in: "query",
                description: "Поле для сортировки",
                schema: new OA\Schema(type: "string", enum: ["id", "name", "created_at", "updated_at"], example: "created_at")
            ),
            new OA\Parameter(
                name: "sort_direction",
                in: "query",
                description: "Направление сортировки",
                schema: new OA\Schema(type: "string", enum: ["asc", "desc"], example: "desc")
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Успешный ответ",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Data retrieved successfully"),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(ref: "#/components/schemas/Employee")
                        ),
                        new OA\Property(
                            property: "meta",
                            type: "object",
                            properties: [
                                new OA\Property(property: "pagination", ref: "#/components/schemas/PaginationMeta")
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Ошибка сервера",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            )
        ]
    )]
    public function index(Request $request, int $id): JsonResponse
    {
        try {
            $query = new GetEmployeesQuery(
                page: max(1, (int) $request->get('page', 1)),
                per_page: min(
                    max(ApiConstants::MIN_PER_PAGE, (int) $request->get('per_page', ApiConstants::DEFAULT_PER_PAGE)),
                    ApiConstants::MAX_PER_PAGE
                ),
                search: $request->get('search'),
                project_id: $id,
                sort_by: $request->get('sort_by', 'created_at'),
                sort_direction: in_array($request->get('sort_direction'), [ApiConstants::SORT_ASC, ApiConstants::SORT_DESC])
                    ? $request->get('sort_direction')
                    : ApiConstants::SORT_DESC,
            );

            $result = $this->employeeService->getEmployees($query);
            $resultArray = $result->toArray();

            return SuccessResponse::paginated(
                data: $resultArray['data'],
                pagination: $resultArray['pagination']
            );
        } catch (\Throwable $e) {
            return ErrorResponse::serverError(exception: $e);
        }
    }
}
