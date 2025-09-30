<?php

namespace App\Services\Payment;

use App\DTOs\PaymentWebhookDTO;
use App\Enums\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TBankWebhookStrategy implements WebhookStrategyInterface
{
    private string $password;

    public function __construct()
    {
        $this->password = config('services.tbank.password');
    }

    public function verifyWebhook(Request $request): bool
    {
        $data = $request->all();
        $receivedToken = $data['Token'] ?? null;

        if (!$receivedToken) {
            Log::warning('T-Bank webhook: Token not found in request');
            return false;
        }

        // Генерируем токен для проверки
        $expectedToken = $this->generateWebhookToken($data);

        $isValid = hash_equals($expectedToken, $receivedToken);

        if (!$isValid) {
            Log::warning('T-Bank webhook: Token mismatch', [
                'expected' => $expectedToken,
                'received' => $receivedToken,
            ]);
        }

        return $isValid;
    }

    public function parseWebhook(Request $request): PaymentWebhookDTO
    {
        $data = $request->all();

        // Маппинг статусов T-Bank в наши статусы
        $status = $this->mapStatus($data['Status'] ?? 'UNKNOWN');

        return new PaymentWebhookDTO(
            external_payment_id: (string) ($data['PaymentId'] ?? ''),
            status: $status,
            error_code: $data['ErrorCode'] ?? null,
            error_message: $data['Message'] ?? null,
            additional_data: [
                'order_id' => $data['OrderId'] ?? null,
                'terminal_key' => $data['TerminalKey'] ?? null,
                'amount' => $data['Amount'] ?? null,
                'card_id' => $data['CardId'] ?? null,
                'pan' => $data['Pan'] ?? null,
                'exp_date' => $data['ExpDate'] ?? null,
                'rebill_id' => $data['RebillId'] ?? null,
                'success' => $data['Success'] ?? null,
            ],
        );
    }

    private function generateWebhookToken(array $data): string
    {
        // Убираем Token из данных для подписи
        unset($data['Token']);

        // Добавляем пароль
        $data['Password'] = $this->password;

        // Сортируем по ключам
        ksort($data);

        // Создаем строку для подписи (конкатенируем только значения)
        $signString = '';
        foreach ($data as $key => $value) {
            // Пропускаем массивы и объекты
            if (is_array($value) || is_object($value)) {
                continue;
            }

            // Преобразуем boolean в строку согласно документации T-Bank
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }

            $signString .= $value;
        }

        return hash('sha256', $signString);
    }

    private function mapStatus(string $tbankStatus): string
    {
        return match ($tbankStatus) {
            'NEW', 'FORM_SHOWED', '3DS_CHECKING', '3DS_CHECKED', 'AUTHORIZING' => PaymentStatus::PENDING->value,
            'AUTHORIZED', 'CONFIRMED' => PaymentStatus::COMPLETED->value,
            'REVERSING', 'PARTIAL_REVERSED', 'REVERSED', 'REFUNDING', 'PARTIAL_REFUNDED', 'REFUNDED' => PaymentStatus::CANCELLED->value,
            'REJECTED', 'DEADLINE_EXPIRED', 'AUTH_FAIL' => PaymentStatus::FAILED->value,
            default => PaymentStatus::PENDING->value,
        };
    }
}
