<?php

namespace App\Console\Commands;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Console\Command;

class ProcessSubscriptionPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:process-payments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Обрабатывает автоматические платежи по подпискам';

    public function __construct(
        private readonly SubscriptionService $subscriptionService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Начинаем обработку подписок...');

        // Находим активные подписки, у которых пришло время платежа
        $subscriptions = Subscription::where('status', SubscriptionStatus::ACTIVE)
            ->whereNotNull('rebill_id')
            ->where('next_billing_date', '<=', now())
            ->get();

        $this->info("Найдено подписок для обработки: {$subscriptions->count()}");

        $processed = 0;
        $failed = 0;

        foreach ($subscriptions as $subscription) {
            try {
                $this->info("Обработка подписки #{$subscription->id} ({$subscription->email})");

                $this->subscriptionService->processRecurringPayment($subscription);

                $processed++;
                $this->info("✓ Подписка #{$subscription->id} обработана");
            } catch (\Exception $e) {
                $failed++;
                $this->error("✗ Ошибка обработки подписки #{$subscription->id}: {$e->getMessage()}");
            }
        }

        $this->info("Обработано: {$processed}, Ошибок: {$failed}");

        return self::SUCCESS;
    }
}
