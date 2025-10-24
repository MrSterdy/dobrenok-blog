<?php

namespace App\Services\SocialMedia;

use App\Models\Integration;
use App\Models\Post;
use Illuminate\Support\Facades\Log;
use VK\Client\VKApiClient;
use VK\Exceptions\VKApiException;
use VK\Exceptions\VKClientException;

class VkPublisher implements SocialMediaPublisherInterface
{
    private VKApiClient $vk;
    private Integration $integration;

    public function __construct(Integration $integration)
    {
        $this->integration = $integration;
        $this->vk = new VKApiClient();
    }

    public function publish(Post $post): SocialMediaPublishResult
    {
        try {
            $accessToken = $this->integration->getCredential('access_token');
            $groupId = $this->integration->getSetting('group_id');

            if (!$accessToken || !$groupId) {
                return SocialMediaPublishResult::failure('VK: отсутствует токен доступа или ID группы');
            }

            // Формируем текст поста
            $message = $this->formatPostMessage($post);

            // Параметры для публикации (без фото пока)
            $params = [
                'owner_id' => -abs($groupId), // Отрицательный ID для группы
                'from_group' => 1,
                'message' => $message,
            ];

            // TODO: для фото нужно сначала загрузить через photos.getWallUploadServer
            // и потом прикрепить через attachments

            // Публикуем пост
            $response = $this->vk->wall()->post($accessToken, $params);

            if (isset($response['post_id'])) {
                $postId = $response['post_id'];
                $postUrl = "https://vk.com/wall-{$groupId}_{$postId}";

                Log::info('Post published to VK', [
                    'post_id' => $post->id,
                    'vk_post_id' => $postId,
                    'url' => $postUrl,
                ]);

                return SocialMediaPublishResult::success(
                    externalId: (string) $postId,
                    externalUrl: $postUrl
                );
            }

            return SocialMediaPublishResult::failure('VK: не удалось получить ID созданного поста');
        } catch (VKApiException $e) {
            Log::error('VK API error', [
                'post_id' => $post->id,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return SocialMediaPublishResult::failure("VK API Error: {$e->getMessage()}");
        } catch (VKClientException $e) {
            Log::error('VK Client error', [
                'post_id' => $post->id,
                'error' => $e->getMessage(),
            ]);

            return SocialMediaPublishResult::failure("VK Client Error: {$e->getMessage()}");
        } catch (\Exception $e) {
            Log::error('Unexpected error publishing to VK', [
                'post_id' => $post->id,
                'error' => $e->getMessage(),
            ]);

            return SocialMediaPublishResult::failure("Ошибка: {$e->getMessage()}");
        }
    }

    public function supports(string $type): bool
    {
        return $type === 'vk';
    }

    public function getType(): string
    {
        return 'vk';
    }

    private function formatPostMessage(Post $post): string
    {
        $message = "📰 {$post->title}\n\n";

        if ($post->sub_title) {
            $message .= "{$post->sub_title}\n\n";
        }

        // Добавляем содержание поста (очищаем от HTML и обрезаем)
        if ($post->body) {
            $body = strip_tags($post->body);
            $body = html_entity_decode($body, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $body = trim(preg_replace('/\s+/', ' ', $body)); // Убираем лишние пробелы

            // Обрезаем до 1000 символов для VK
            if (mb_strlen($body) > 1000) {
                $body = mb_substr($body, 0, 1000) . '...';
            }

            if ($body) {
                $message .= "{$body}\n\n";
            }
        }

        // Добавляем ссылку на полный пост на фронтенде
        $frontendUrl = config('app.frontend_url', env('FRONTEND_URL'));
        $postUrl = rtrim($frontendUrl, '/') . '/news/' . $post->slug;
        $message .= "Читать полностью: {$postUrl}";

        return $message;
    }
}
