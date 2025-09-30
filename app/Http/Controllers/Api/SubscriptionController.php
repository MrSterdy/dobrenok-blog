<?php

namespace App\Http\Controllers\Api;

use App\Commands\CreateSubscriptionCommand;
use App\Enums\PaymentProvider;
use App\Http\Controllers\Controller;
use App\Http\Responses\ErrorResponse;
use App\Http\Responses\SuccessResponse;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Subscriptions", description: "Подписки")]
class SubscriptionController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService
    ) {}

    #[OA\Post(
        path: "/projects/{id}/subscriptions",
        summary: "Создать подписку",
        description: "Создает новую подписку с произвольной суммой для проекта",
        tags: ["Subscriptions"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID проекта",
                schema: new OA\Schema(type: "integer", example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: "object",
                required: ["email", "amount", "currency", "description"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", description: "Email подписчика", example: "user@example.com"),
                    new OA\Property(property: "name", type: "string", nullable: true, description: "Имя подписчика", example: "Иван Иванов"),
                    new OA\Property(property: "amount", type: "number", format: "float", description: "Сумма подписки (любая)", example: 500.00),
                    new OA\Property(property: "currency", type: "string", description: "Валюта (RUB, USD, EUR)", example: "RUB"),
                    new OA\Property(property: "description", type: "string", description: "Описание подписки", example: "Ежемесячная поддержка"),
                    new OA\Property(property: "success_url", type: "string", format: "uri", nullable: true, description: "URL успешной оплаты", example: "https://example.com/success"),
                    new OA\Property(property: "fail_url", type: "string", format: "uri", nullable: true, description: "URL неуспешной оплаты", example: "https://example.com/fail"),
                    new OA\Property(property: "payment_provider", type: "string", description: "Платежная система", enum: ["tbank"], example: "tbank"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Подписка создана",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Resource created successfully"),
                        new OA\Property(property: "data", ref: "#/components/schemas/Subscription")
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Ошибка валидации",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
            new OA\Response(
                response: 500,
                description: "Ошибка сервера",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
        ]
    )]
    public function store(Request $request, int $id): JsonResponse
    {
        try {
            try {
                Validator::validate($request->all(), [
                    'email' => ['required', 'email'],
                    'name' => ['nullable', 'string', 'max:255'],
                    'amount' => ['required', 'numeric', 'min:0.01'],
                    'currency' => ['required', 'string', 'in:RUB,USD,EUR'],
                    'description' => ['required', 'string', 'max:140'],
                    'success_url' => ['nullable', 'url'],
                    'fail_url' => ['nullable', 'url'],
                    'payment_provider' => ['string', Rule::enum(PaymentProvider::class)],
                ]);
            } catch (ValidationException $e) {
                return ErrorResponse::fromValidationException($e);
            }

            $command = new CreateSubscriptionCommand(
                project_id: $id,
                email: (string) $request->input('email'),
                name: $request->input('name'),
                amount: (float) $request->input('amount'),
                currency: (string) $request->input('currency'),
                description: (string) $request->input('description'),
                success_url: $request->input('success_url'),
                fail_url: $request->input('fail_url'),
                payment_provider: PaymentProvider::from($request->input('payment_provider', PaymentProvider::TBANK->value)),
            );

            $dto = $this->subscriptionService->createSubscription($command);

            return SuccessResponse::created(
                data: $dto->toArray()
            );
        } catch (\Throwable $e) {
            return ErrorResponse::serverError(exception: $e);
        }
    }
}
