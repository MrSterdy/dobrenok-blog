<?php

namespace App\Services\SocialMedia;

use App\Models\Post;

interface SocialMediaPublisherInterface
{
    /**
     * Опубликовать пост в социальной сети
     */
    public function publish(Post $post): SocialMediaPublishResult;

    /**
     * Проверить, поддерживается ли тип интеграции
     */
    public function supports(string $type): bool;

    /**
     * Получить тип интеграции
     */
    public function getType(): string;
}
