<?php

namespace App\Http\Controllers\Api;

use App\Commands\CreateApplicationCommand;
// use App\Constants\ApiConstants;
use App\Http\Controllers\Controller;
use App\Http\Responses\ErrorResponse;
use App\Http\Responses\SuccessResponse;
// use App\Queries\GetApplicationsQuery;
use App\Services\ApplicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use Illuminate\Validation\ValidationException;

#[OA\Tag(name: "Applications", description: "Заявки проектов")]
class ApplicationController extends Controller
{
    public function __construct(
        private readonly ApplicationService $applicationService
    ) {}

    // #[OA\Get(
    //     path: "/projects/{id}/applications",
    //     summary: "Список заявок проекта",
    //     tags: ["Applications"],
    //     parameters: [
    //         new OA\Parameter(name: "id", in: "path", required: true, description: "ID проекта", schema: new OA\Schema(type: "integer", example: 1)),
    //         new OA\Parameter(name: "page", in: "query", description: "Номер страницы", schema: new OA\Schema(type: "integer", minimum: 1, example: 1)),
    //         new OA\Parameter(name: "per_page", in: "query", description: "Количество на странице", schema: new OA\Schema(type: "integer", minimum: 1, maximum: 50, example: 15)),
    //         new OA\Parameter(name: "search", in: "query", description: "Поиск по названию", schema: new OA\Schema(type: "string", example: "Обратный звонок")),
    //         new OA\Parameter(name: "sort_by", in: "query", description: "Поле сортировки", schema: new OA\Schema(type: "string", enum: ["id", "name", "created_at", "updated_at"], example: "created_at")),
    //         new OA\Parameter(name: "sort_direction", in: "query", description: "Направление сортировки", schema: new OA\Schema(type: "string", enum: ["asc", "desc"], example: "desc")),
    //     ],
    //     responses: [
    //         new OA\Response(response: 200, description: "Успешный ответ", content: new OA\JsonContent(
    //             type: "object",
    //             properties: [
    //                 new OA\Property(property: "success", type: "boolean", example: true),
    //                 new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/Application")),
    //                 new OA\Property(property: "meta", type: "object", properties: [
    //                     new OA\Property(property: "pagination", ref: "#/components/schemas/PaginationMeta")
    //                 ])
    //             ]
    //         )),
    //         new OA\Response(response: 500, description: "Ошибка сервера", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")),
    //     ]
    // )]
    // public function index(Request $request, int $id): JsonResponse
    // {
    //     try {
    //         $query = new GetApplicationsQuery(
    //             page: max(1, (int) $request->get('page', 1)),
    //             per_page: min(max(ApiConstants::MIN_PER_PAGE, (int) $request->get('per_page', ApiConstants::DEFAULT_PER_PAGE)), ApiConstants::MAX_PER_PAGE),
    //             search: $request->get('search'),
    //             project_id: $id,
    //             sort_by: $request->get('sort_by', 'created_at'),
    //             sort_direction: in_array($request->get('sort_direction'), [ApiConstants::SORT_ASC, ApiConstants::SORT_DESC]) ? $request->get('sort_direction') : ApiConstants::SORT_DESC,
    //         );

    //         $result = $this->applicationService->getApplications($query);
    //         $resultArray = $result->toArray();

    //         return SuccessResponse::paginated(
    //             data: $resultArray['data'],
    //             pagination: $resultArray['pagination']
    //         );
    //     } catch (\Throwable $e) {
    //         return ErrorResponse::serverError(exception: $e);
    //     }
    // }

    #[OA\Post(
        path: "/projects/{id}/applications",
        summary: "Создать заявку",
        tags: ["Applications"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, description: "ID проекта", schema: new OA\Schema(type: "integer", example: 1)),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            type: "object",
            required: ["name", "data"],
            properties: [
                new OA\Property(property: "name", type: "string", example: "Обратный звонок"),
                new OA\Property(property: "data", type: "object", example: ["full_name" => "Иван Иванов", "email" => "ivan@example.com", "phone" => "+79990000000"]),
            ]
        )),
        responses: [
            new OA\Response(response: 201, description: "Создано", content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(property: "message", type: "string", example: "Resource created successfully"),
                    new OA\Property(property: "data", ref: "#/components/schemas/Application")
                ]
            )),
            new OA\Response(response: 422, description: "Ошибка валидации", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")),
            new OA\Response(response: 500, description: "Ошибка сервера", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")),
        ]
    )]
    public function store(Request $request, int $id): JsonResponse
    {
        try {
            try {
                Validator::validate($request->all(), [
                    'name' => ['required', 'string', 'max:255'],
                    'data' => ['required', 'array'],
                ]);
            } catch (ValidationException $e) {
                return ErrorResponse::fromValidationException($e);
            }

            $dto = $this->applicationService->create(new CreateApplicationCommand(
                project_id: $id,
                name: (string) $request->input('name'),
                data: (array) $request->input('data', []),
            ));

            return SuccessResponse::created(
                data: $dto->toArray()
            );
        } catch (\Throwable $e) {
            return ErrorResponse::serverError(exception: $e);
        }
    }
}
