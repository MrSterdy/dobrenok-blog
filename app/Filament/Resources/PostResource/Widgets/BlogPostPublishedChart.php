<?php

namespace App\Filament\Resources\PostResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use App\Models\Post;

class BlogPostPublishedChart extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            BaseWidget\Stat::make('Опубликованные посты', Post::published()->count()),
            BaseWidget\Stat::make('Запланированные посты', Post::scheduled()->count()),
            BaseWidget\Stat::make('Ожидающие публикации посты', Post::pending()->count()),
        ];
    }
}
