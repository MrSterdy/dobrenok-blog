<?php

namespace App\Services\Payment;

use App\Commands\CreatePaymentCommand;
use App\DTOs\PaymentCreationResultDTO;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TBankPaymentStrategy implements PaymentStrategyInterface
{
    private string $terminalKey;
    private string $password;
    private string $apiUrl;

    public function __construct()
    {
        $this->terminalKey = config('services.tbank.terminal_key');
        $this->password = config('services.tbank.password');
        $this->apiUrl = config('services.tbank.api_url', 'https://securepay.tinkoff.ru/v2');
    }

    public function createPayment(CreatePaymentCommand $command): PaymentCreationResultDTO
    {
        $orderId = Str::uuid()->toString();
        $amountInKopecks = (int) ($command->amount * 100);

        $data = [
            'TerminalKey' => $this->terminalKey,
            'Amount' => $amountInKopecks,
            'OrderId' => $orderId,
            'Description' => $command->description,
            'Currency' => $this->getCurrencyCode($command->currency),
            'NotificationURL' => $this->getNotificationUrl(),
        ];

        if ($command->success_url) {
            $data['SuccessURL'] = $command->success_url;
        }

        if ($command->fail_url) {
            $data['FailURL'] = $command->fail_url;
        }

        // Добавляем чек (обязательно для 54-ФЗ)
        $data['Receipt'] = $this->generateReceipt($command);

        // Генерируем подпись согласно документации T-Bank
        $data['Token'] = $this->generateToken($data);

        $response = Http::withOptions([
            'verify' => config('app.env') === 'production', // Проверяем SSL только в продакшене
            'timeout' => 30,
            'connect_timeout' => 10,
        ])->post($this->apiUrl . '/Init', $data);

        if (!$response->successful()) {
            throw new \Exception('T-Bank API HTTP error: ' . $response->status() . ' - ' . $response->body());
        }

        $responseData = $response->json();

        if (!isset($responseData['Success']) || !$responseData['Success']) {
            $errorMessage = $responseData['Message'] ?? $responseData['Details'] ?? 'Unknown error';
            throw new \Exception('T-Bank payment creation failed: ' . $errorMessage);
        }

        return new PaymentCreationResultDTO(
            external_payment_id: $responseData['PaymentId'],
            status: 'pending',
            payment_url: $responseData['PaymentURL'] ?? null,
            additional_data: [
                'order_id' => $orderId,
                'terminal_key' => $this->terminalKey,
            ]
        );
    }

    private function generateToken(array $data): string
    {
        // Убираем Token из данных для подписи
        unset($data['Token']);

        // Добавляем пароль
        $data['Password'] = $this->password;

        // Сортируем по ключам
        ksort($data);

        // Создаем строку для подписи
        $signString = '';
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                continue; // Пропускаем массивы согласно документации
            }
            $signString .= $value;
        }

        return hash('sha256', $signString);
    }

    private function getCurrencyCode(string $currency): string
    {
        return match (strtoupper($currency)) {
            'RUB' => '643',
            'USD' => '840',
            'EUR' => '978',
            default => '643', // По умолчанию рубли
        };
    }

    private function generateReceipt(CreatePaymentCommand $command): array
    {
        $amountInKopecks = (int) ($command->amount * 100);

        $receipt = [
            'Email' => $command->customer_email ?? config('mail.from.address'),
            'Taxation' => config('services.tbank.receipt.taxation', 'usn_income'),
            'Items' => [
                [
                    'Name' => $command->description,
                    'Price' => $amountInKopecks,
                    'Quantity' => 1,
                    'Amount' => $amountInKopecks,
                    'Tax' => config('services.tbank.receipt.tax', 'none'),
                    'PaymentMethod' => config('services.tbank.receipt.payment_method', 'full_payment'),
                    'PaymentObject' => config('services.tbank.receipt.payment_object', 'service'),
                ],
            ],
        ];

        // Добавляем телефон, если указан
        if ($command->customer_phone) {
            $receipt['Phone'] = $command->customer_phone;
        }

        return $receipt;
    }

    private function getNotificationUrl(): string
    {
        $appUrl = config('app.url');
        return $appUrl . '/api/v1/webhooks/payments/tbank';
    }
}
