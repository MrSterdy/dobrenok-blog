<?php

namespace App\Services\SocialMedia;

use App\Models\Integration;
use App\Models\Post;
use Illuminate\Support\Facades\Log;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;

class TelegramPublisher implements SocialMediaPublisherInterface
{
    private BotApi $telegram;
    private Integration $integration;

    public function __construct(Integration $integration)
    {
        $this->integration = $integration;
        $botToken = $integration->getCredential('bot_token');

        if (!$botToken) {
            throw new \RuntimeException('Telegram: отсутствует токен бота');
        }

        $this->telegram = new BotApi($botToken);
    }

    public function publish(Post $post): SocialMediaPublishResult
    {
        try {
            $channelId = $this->integration->getSetting('channel_id');

            if (!$channelId) {
                return SocialMediaPublishResult::failure('Telegram: отсутствует ID канала');
            }

            // Формируем текст сообщения
            $message = $this->formatPostMessage($post);

            // Публикуем только текст (без фото, так как Telegram требует прямую ссылку)
            // TODO: для фото нужно загружать файл через InputFile или использовать публичный URL
            $sentMessage = $this->telegram->sendMessage(
                $channelId,
                $message,
                'HTML',
                false,
                null,
                null,
                false
            );

            if ($sentMessage && $sentMessage->getMessageId()) {
                $messageId = $sentMessage->getMessageId();

                // Формируем ссылку на пост в канале
                $channelUsername = $this->integration->getSetting('channel_username');
                $postUrl = $channelUsername
                    ? "https://t.me/{$channelUsername}/{$messageId}"
                    : null;

                Log::info('Post published to Telegram', [
                    'post_id' => $post->id,
                    'telegram_message_id' => $messageId,
                    'url' => $postUrl,
                ]);

                return SocialMediaPublishResult::success(
                    externalId: (string) $messageId,
                    externalUrl: $postUrl ?? "message_{$messageId}"
                );
            }

            return SocialMediaPublishResult::failure('Telegram: не удалось отправить сообщение');
        } catch (Exception $e) {
            Log::error('Telegram API error', [
                'post_id' => $post->id,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return SocialMediaPublishResult::failure("Telegram API Error: {$e->getMessage()}");
        } catch (InvalidArgumentException $e) {
            Log::error('Telegram invalid argument', [
                'post_id' => $post->id,
                'error' => $e->getMessage(),
            ]);

            return SocialMediaPublishResult::failure("Telegram Error: {$e->getMessage()}");
        } catch (\Exception $e) {
            Log::error('Unexpected error publishing to Telegram', [
                'post_id' => $post->id,
                'error' => $e->getMessage(),
            ]);

            return SocialMediaPublishResult::failure("Ошибка: {$e->getMessage()}");
        }
    }

    public function supports(string $type): bool
    {
        return $type === 'telegram';
    }

    public function getType(): string
    {
        return 'telegram';
    }

    private function formatPostMessage(Post $post): string
    {
        $message = "<b>📰 {$post->title}</b>\n\n";

        if ($post->sub_title) {
            $message .= "<i>{$post->sub_title}</i>\n\n";
        }

        // Добавляем содержание поста (очищаем от HTML и обрезаем)
        if ($post->body) {
            $body = strip_tags($post->body);
            $body = html_entity_decode($body, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $body = trim(preg_replace('/\s+/', ' ', $body)); // Убираем лишние пробелы

            // Обрезаем до 500 символов для Telegram (лимит 1000, но оставляем место для остального)
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
        $message .= "🔗 <a href=\"{$postUrl}\">Читать полностью</a>";

        return $message;
    }
}
