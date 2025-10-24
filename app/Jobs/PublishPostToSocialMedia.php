<?php

namespace App\Jobs;

use App\Models\Integration;
use App\Models\Post;
use App\Models\PostPublication;
use App\Services\SocialMedia\SocialMediaPublisherFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PublishPostToSocialMedia implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Количество попыток выполнения задачи
     */
    public int $tries = 3;

    /**
     * Время ожидания перед повторной попыткой (в секундах)
     */
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Post $post,
        public Integration $integration
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Проверяем, что интеграция активна
            if (!$this->integration->is_active) {
                Log::warning('Integration is not active', [
                    'post_id' => $this->post->id,
                    'integration_id' => $this->integration->id,
                ]);
                return;
            }

            // Создаем или обновляем запись о публикации
            $publication = PostPublication::firstOrCreate(
                [
                    'post_id' => $this->post->id,
                    'integration_id' => $this->integration->id,
                ],
                [
                    'status' => 'pending',
                ]
            );

            // Если уже опубликовано, пропускаем
            if ($publication->isPublished()) {
                Log::info('Post already published', [
                    'post_id' => $this->post->id,
                    'integration_id' => $this->integration->id,
                ]);
                return;
            }

            // Создаем издателя через фабрику
            $publisher = SocialMediaPublisherFactory::create($this->integration);

            // Публикуем пост
            $result = $publisher->publish($this->post);

            if ($result->success) {
                // Обновляем запись об успешной публикации
                $publication->update([
                    'status' => 'published',
                    'external_id' => $result->external_id,
                    'external_url' => $result->external_url,
                    'published_at' => now(),
                    'error_message' => null,
                ]);

                Log::info('Post successfully published to social media', [
                    'post_id' => $this->post->id,
                    'integration_type' => $this->integration->type,
                    'external_url' => $result->external_url,
                ]);
            } else {
                // Увеличиваем счетчик попыток
                $publication->increment('retry_count');

                // Обновляем статус на failed если достигли лимита попыток
                if ($publication->retry_count >= $this->tries) {
                    $publication->update([
                        'status' => 'failed',
                        'error_message' => $result->error_message,
                    ]);

                    Log::error('Post publication failed after max retries', [
                        'post_id' => $this->post->id,
                        'integration_type' => $this->integration->type,
                        'error' => $result->error_message,
                        'retries' => $publication->retry_count,
                    ]);
                } else {
                    $publication->update([
                        'error_message' => $result->error_message,
                    ]);

                    Log::warning('Post publication failed, will retry', [
                        'post_id' => $this->post->id,
                        'integration_type' => $this->integration->type,
                        'error' => $result->error_message,
                        'retry' => $publication->retry_count,
                    ]);
                }

                // Выбрасываем исключение для повторной попытки
                throw new \RuntimeException($result->error_message);
            }
        } catch (\Exception $e) {
            Log::error('Error in PublishPostToSocialMedia job', [
                'post_id' => $this->post->id,
                'integration_id' => $this->integration->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('PublishPostToSocialMedia job failed permanently', [
            'post_id' => $this->post->id,
            'integration_id' => $this->integration->id,
            'error' => $exception->getMessage(),
        ]);

        // Обновляем статус публикации на failed
        PostPublication::where('post_id', $this->post->id)
            ->where('integration_id', $this->integration->id)
            ->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
            ]);
    }
}
