<?php

namespace App\Observers;

use App\Jobs\PublishPostToSocialMedia;
use App\Models\Integration;
use App\Models\Post;
use App\Enums\PostStatus;
use Illuminate\Support\Facades\Log;

class PostObserver
{
    /**
     * Handle the Post "created" event.
     */
    public function created(Post $post): void
    {
        // Публикуем только если пост сразу создан как опубликованный
        if ($post->status === PostStatus::PUBLISHED) {
            // Получаем настройки публикации из формы (передаются через request)
            $publishSettings = $this->getPublishSettingsFromRequest();
            $this->publishToSocialMedia($post, $publishSettings);
        }
    }

    /**
     * Handle the Post "updated" event.
     */
    public function updated(Post $post): void
    {
        // Проверяем, изменился ли статус на опубликованный
        if ($post->wasChanged('status') && $post->status === PostStatus::PUBLISHED) {
            // Получаем настройки публикации из формы
            $publishSettings = $this->getPublishSettingsFromRequest();
            $this->publishToSocialMedia($post, $publishSettings);
        }
    }

    /**
     * Получить настройки публикации из HTTP-запроса
     */
    private function getPublishSettingsFromRequest(): array
    {
        $settings = [];

        // Получаем все активные интеграции
        $integrations = Integration::where('is_active', true)->get();

        foreach ($integrations as $integration) {
            $fieldName = "publish_to_{$integration->type}";

            // Если поле есть в запросе, используем его значение
            if (request()->has($fieldName)) {
                $settings[$integration->type] = (bool) request()->input($fieldName, false);
            } else {
                // По умолчанию публикуем везде (если настройки не переданы)
                $settings[$integration->type] = true;
            }
        }

        return $settings;
    }

    /**
     * Отправить пост в социальные сети
     */
    private function publishToSocialMedia(Post $post, array $publishSettings = []): void
    {
        try {
            // Получаем все активные интеграции
            $integrations = Integration::where('is_active', true)->get();

            if ($integrations->isEmpty()) {
                Log::info('No active integrations found for post publication', [
                    'post_id' => $post->id,
                ]);
                return;
            }

            // Отправляем задачи в очередь для каждой активной интеграции
            foreach ($integrations as $integration) {
                // Проверяем, не был ли уже опубликован в эту соцсеть
                if ($post->isPublishedTo($integration->id)) {
                    Log::info('Post already published to this integration', [
                        'post_id' => $post->id,
                        'integration_id' => $integration->id,
                        'integration_type' => $integration->type,
                    ]);
                    continue;
                }

                // Проверяем, нужно ли публиковать в эту соцсеть
                if (!$this->shouldPublish($integration->type, $publishSettings)) {
                    Log::info('Post publication skipped by user preference', [
                        'post_id' => $post->id,
                        'integration_type' => $integration->type,
                    ]);
                    continue;
                }

                PublishPostToSocialMedia::dispatch($post, $integration)
                    ->onQueue('social-media');

                Log::info('Queued post for social media publication', [
                    'post_id' => $post->id,
                    'integration_type' => $integration->type,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error queuing post for social media publication', [
                'post_id' => $post->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Проверить, нужно ли публиковать в данную соцсеть
     */
    private function shouldPublish(string $integrationType, array $publishSettings): bool
    {
        return $publishSettings[$integrationType] ?? false;
    }
}
