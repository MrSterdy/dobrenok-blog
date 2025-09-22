<?php

namespace App\Http\Controllers\Api;

use App\Constants\ApiConstants;
use App\Http\Controllers\Controller;
use App\Http\Responses\ErrorResponse;
use App\Http\Responses\SuccessResponse;
use App\Queries\GetEventsQuery;
use App\Services\EventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Events", description: "Мероприятия проектов")]
class EventController extends Controller
{
    public function __construct(
        private readonly EventService $eventService
    ) {}

    #[OA\Get(
        path: "/projects/{id}/events",
        summary: "Список мероприятий проекта",
        description: "Возвращает пагинированный список мероприятий для указанного проекта",
        tags: ["Events"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID проекта",
                schema: new OA\Schema(type: "integer", example: 1)
            ),
            new OA\Parameter(
                name: "month",
                in: "query",
                description: "Месяц (1-12) для фильтрации по дате начала",
                schema: new OA\Schema(type: "integer", minimum: 1, maximum: 12, example: 9)
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
                description: "Поиск по названию или описанию",
                schema: new OA\Schema(type: "string", example: "митап")
            ),
            new OA\Parameter(
                name: "sort_by",
                in: "query",
                description: "Поле для сортировки",
                schema: new OA\Schema(type: "string", enum: ["id", "name", "start_date", "created_at", "updated_at"], example: "start_date")
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
                            items: new OA\Items(ref: "#/components/schemas/Event")
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
            $query = new GetEventsQuery(
                page: max(1, (int) $request->get('page', 1)),
                per_page: min(
                    max(ApiConstants::MIN_PER_PAGE, (int) $request->get('per_page', ApiConstants::DEFAULT_PER_PAGE)),
                    ApiConstants::MAX_PER_PAGE
                ),
                search: $request->get('search'),
                project_id: $id,
                month: $request->get('month') ? max(1, min(12, (int) $request->get('month'))) : null,
                sort_by: $request->get('sort_by', 'start_date'),
                sort_direction: in_array($request->get('sort_direction'), [ApiConstants::SORT_ASC, ApiConstants::SORT_DESC])
                    ? $request->get('sort_direction')
                    : ApiConstants::SORT_DESC,
            );

            $result = $this->eventService->getEvents($query);
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
