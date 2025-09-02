<?php

namespace App\Models;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Firefly\FilamentBlog\Models\SeoDetail as FilamentBlogSeoDetail;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeoDetail extends FilamentBlogSeoDetail
{
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class)->orderByDesc('id');
    }

    public static function getForm()
    {
        return [
            Select::make('post_id')
                ->label('Пост')
                ->createOptionForm(Post::getForm())
                ->editOptionForm(Post::getForm())
                ->relationship('post', 'title')
                ->unique(config('filamentblog.tables.prefix') . 'seo_details', 'post_id', null, 'id')
                ->required()
                ->preload()
                ->searchable()
                ->default(request('post_id') ?? '')
                ->columnSpanFull(),
            TextInput::make('title')
                ->label('Заголовок')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
            TagsInput::make('keywords')
                ->label('Ключевые слова')
                ->columnSpanFull(),
            Textarea::make('description')
                ->label('Описание')
                ->required()
                ->maxLength(65535)
                ->columnSpanFull(),
        ];
    }
}
