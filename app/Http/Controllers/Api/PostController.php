<?php

namespace App\Http\Controllers\Api;

use App\Constants\ApiConstants;
use App\Http\Controllers\Controller;
use App\Http\Responses\ErrorResponse;
use App\Http\Responses\SuccessResponse;
use App\Queries\GetPostBySlugQuery;
use App\Queries\GetPostsQuery;
use App\Services\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Posts", description: "Управление постами блога")]
class PostController extends Controller
{
    public function __construct(
        private readonly PostService $postService
    ) {}

    #[OA\Get(
        path: "/projects/{id}/posts",
        summary: "Получить список постов проекта",
        description: "Возвращает пагинированный список постов проекта с возможностью поиска, фильтрации и сортировки",
        tags: ["Posts"],
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
                description: "Поиск по заголовку, подзаголовку или содержимому поста",
                schema: new OA\Schema(type: "string", example: "Laravel")
            ),
            new OA\Parameter(
                name: "category_id",
                in: "query",
                description: "Фильтр по ID категории",
                schema: new OA\Schema(type: "integer", example: 1)
            ),
            new OA\Parameter(
                name: "tag_id",
                in: "query",
                description: "Фильтр по ID тега",
                schema: new OA\Schema(type: "integer", example: 1)
            ),
            new OA\Parameter(
                name: "status",
                in: "query",
                description: "Статус поста",
                schema: new OA\Schema(type: "string", enum: ["published", "pending", "scheduled"], example: "published")
            ),
            new OA\Parameter(
                name: "sort_by",
                in: "query",
                description: "Поле для сортировки",
                schema: new OA\Schema(type: "string", enum: ["id", "title", "published_at", "created_at", "updated_at"], example: "published_at")
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
                        new OA\Property(property: "message", type: "string", example: "Posts retrieved successfully"),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(ref: "#/components/schemas/Post")
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
            $query = new GetPostsQuery(
                page: max(1, (int) $request->get('page', 1)),
                per_page: min(
                    max(ApiConstants::MIN_PER_PAGE, (int) $request->get('per_page', ApiConstants::DEFAULT_PER_PAGE)),
                    ApiConstants::MAX_PER_PAGE
                ),
                search: $request->get('search'),
                project_id: $id,
                category_id: $request->get('category_id') ? (int) $request->get('category_id') : null,
                tag_id: $request->get('tag_id') ? (int) $request->get('tag_id') : null,
                status: in_array($request->get('status'), [ApiConstants::POST_STATUS_PUBLISHED, ApiConstants::POST_STATUS_PENDING, ApiConstants::POST_STATUS_SCHEDULED])
                    ? $request->get('status')
                    : ApiConstants::POST_STATUS_PUBLISHED,
                sort_by: $request->get('sort_by', 'published_at'),
                sort_direction: in_array($request->get('sort_direction'), [ApiConstants::SORT_ASC, ApiConstants::SORT_DESC])
                    ? $request->get('sort_direction')
                    : ApiConstants::SORT_DESC,
            );

            $result = $this->postService->getPosts($query);
            $resultArray = $result->toArray();

            return SuccessResponse::paginated(
                data: $resultArray['data'],
                pagination: $resultArray['pagination'],
                message: ApiConstants::SUCCESS_POSTS_RETRIEVED
            );
        } catch (\Throwable $e) {
            return ErrorResponse::serverError(exception: $e);
        }
    }

    #[OA\Get(
        path: "/projects/{id}/posts/{slug}",
        summary: "Получить пост проекта по slug",
        description: "Возвращает информацию о конкретном посте проекта",
        tags: ["Posts"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID проекта",
                schema: new OA\Schema(type: "integer", example: 1)
            ),
            new OA\Parameter(
                name: "slug",
                in: "path",
                required: true,
                description: "Slug поста",
                schema: new OA\Schema(type: "string", example: "moy-pervyy-post")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Успешный ответ",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Post retrieved successfully"),
                        new OA\Property(property: "data", ref: "#/components/schemas/Post")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Пост не найден",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
            new OA\Response(
                response: 500,
                description: "Ошибка сервера",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            )
        ]
    )]
    public function show(int $id, string $slug): JsonResponse
    {
        try {
            $query = new GetPostBySlugQuery($slug, $id);
            $post = $this->postService->getPostBySlug($query);

            if (!$post) {
                return ErrorResponse::notFound(ApiConstants::ERROR_POST_NOT_FOUND);
            }

            return SuccessResponse::make(
                data: $post->toArray(),
                message: ApiConstants::SUCCESS_POST_RETRIEVED
            );
        } catch (\Throwable $e) {
            return ErrorResponse::serverError(exception: $e);
        }
    }
}
