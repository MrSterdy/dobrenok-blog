<?php

namespace App\Models;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Set;
use Firefly\FilamentBlog\Models\Tag as FilamentBlogTag;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Tag extends FilamentBlogTag
{
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, config('filamentblog.tables.prefix') . 'post_' . config('filamentblog.tables.prefix') . 'tag');
    }

    public static function getForm(): array
    {
        return [
            TextInput::make('name')
                ->label('Название')
                ->live(true)->afterStateUpdated(fn(Set $set, ?string $state) => $set(
                    'slug',
                    Str::slug($state)
                ))
                ->unique(config('filamentblog.tables.prefix') . 'tags', 'name', null, 'id')
                ->required()
                ->maxLength(50),

            TextInput::make('slug')
                ->label('Ссылка')
                ->unique(config('filamentblog.tables.prefix') . 'tags', 'slug', null, 'id')
                ->readOnly()
                ->maxLength(155),
        ];
    }
}
