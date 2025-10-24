<?php

namespace App\Services\SocialMedia;

use App\Models\Integration;

class SocialMediaPublisherFactory
{
    /**
     * Создать издателя для конкретной интеграции
     */
    public static function create(Integration $integration): SocialMediaPublisherInterface
    {
        return match ($integration->type) {
            'vk' => new VkPublisher($integration),
            'telegram' => new TelegramPublisher($integration),
            default => throw new \InvalidArgumentException("Unsupported integration type: {$integration->type}"),
        };
    }

    /**
     * Получить все доступные типы интеграций
     */
    public static function getAvailableTypes(): array
    {
        return [
            'vk' => 'ВКонтакте',
            'telegram' => 'Telegram',
        ];
    }
}
