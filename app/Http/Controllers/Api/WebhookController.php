<?php

namespace App\Http\Controllers\Api;

use App\Enums\PaymentProvider;
use App\Http\Controllers\Controller;
use App\Services\WebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Webhooks", description: "Вебхуки платежных систем")]
class WebhookController extends Controller
{
    public function __construct(
        private readonly WebhookService $webhookService
    ) {}

    #[OA\Post(
        path: "/webhooks/payments/{provider}",
        summary: "Обработка вебхука от платежной системы",
        description: "Принимает уведомления о статусе платежа от платежных систем",
        tags: ["Webhooks"],
        parameters: [
            new OA\Parameter(
                name: "provider",
                in: "path",
                required: true,
                description: "Идентификатор платежной системы (tbank)",
                schema: new OA\Schema(type: "string", enum: ["tbank"], example: "tbank")
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Вебхук успешно обработан",
                content: new OA\JsonContent(
                    type: "string",
                    example: "OK"
                )
            ),
            new OA\Response(
                response: 400,
                description: "Ошибка обработки вебхука",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "error", type: "string", example: "Invalid signature")
                    ]
                )
            ),
        ]
    )]
    public function handlePaymentWebhook(Request $request, string $provider): JsonResponse|string
    {
        try {
            // Пытаемся получить provider из enum
            $paymentProvider = PaymentProvider::tryFrom($provider);

            if (!$paymentProvider) {
                Log::warning('Unknown payment provider in webhook', [
                    'provider' => $provider,
                    'request' => $request->all(),
                ]);
                return response()->json(['error' => 'Unknown payment provider'], 400);
            }

            // Обрабатываем вебхук
            $success = $this->webhookService->processWebhook($paymentProvider, $request);

            if (!$success) {
                return response()->json(['error' => 'Webhook processing failed'], 400);
            }

            // Возвращаем OK согласно документации T-Bank
            return response('OK', 200)
                ->header('Content-Type', 'text/plain');
        } catch (\Throwable $e) {
            Log::error('Webhook processing error', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
}
