<?php

namespace App\Http\Controllers\Api;

use App\Commands\CreatePaymentCommand;
use App\Enums\PaymentProvider;
use App\Http\Controllers\Controller;
use App\Http\Responses\ErrorResponse;
use App\Http\Responses\SuccessResponse;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Payments", description: "Платежи")]
class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService
    ) {}

    #[OA\Post(
        path: "/projects/{id}/payments",
        summary: "Создать платеж",
        description: "Создает новый платеж для проекта через выбранную платежную систему",
        tags: ["Payments"],
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
                required: ["amount", "currency", "description"],
                properties: [
                    new OA\Property(property: "amount", type: "number", format: "float", description: "Сумма платежа", example: 1000.50),
                    new OA\Property(property: "currency", type: "string", description: "Валюта (RUB, USD, EUR)", example: "RUB"),
                    new OA\Property(property: "description", type: "string", description: "Описание платежа", example: "Оплата услуг"),
                    new OA\Property(property: "customer_email", type: "string", format: "email", nullable: true, description: "Email покупателя для чека", example: "user@example.com"),
                    new OA\Property(property: "customer_phone", type: "string", nullable: true, description: "Телефон покупателя для чека", example: "+79001234567"),
                    new OA\Property(property: "success_url", type: "string", format: "uri", nullable: true, description: "URL успешной оплаты", example: "https://example.com/success"),
                    new OA\Property(property: "fail_url", type: "string", format: "uri", nullable: true, description: "URL неуспешной оплаты", example: "https://example.com/fail"),
                    new OA\Property(property: "payment_provider", type: "string", description: "Платежная система", enum: ["tbank"], example: "tbank"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Платеж создан",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Resource created successfully"),
                        new OA\Property(property: "data", ref: "#/components/schemas/Payment")
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
                    'amount' => ['required', 'numeric', 'min:0.01'],
                    'currency' => ['required', 'string', 'in:RUB,USD,EUR'],
                    'description' => ['required', 'string', 'max:140'],
                    'customer_email' => ['nullable', 'email'],
                    'customer_phone' => ['nullable', 'string', 'max:20'],
                    'success_url' => ['nullable', 'url'],
                    'fail_url' => ['nullable', 'url'],
                    'payment_provider' => ['string', Rule::enum(PaymentProvider::class)],
                ]);
            } catch (ValidationException $e) {
                return ErrorResponse::fromValidationException($e);
            }

            $command = new CreatePaymentCommand(
                project_id: $id,
                amount: (float) $request->input('amount'),
                currency: (string) $request->input('currency'),
                description: (string) $request->input('description'),
                customer_email: $request->input('customer_email'),
                customer_phone: $request->input('customer_phone'),
                success_url: $request->input('success_url'),
                fail_url: $request->input('fail_url'),
                payment_provider: PaymentProvider::from($request->input('payment_provider', PaymentProvider::TBANK->value)),
            );

            $dto = $this->paymentService->createPayment($command);

            return SuccessResponse::created(
                data: $dto->toArray()
            );
        } catch (\Throwable $e) {
            return ErrorResponse::serverError(exception: $e);
        }
    }
}
