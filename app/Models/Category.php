<?php

namespace App\Models;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Firefly\FilamentBlog\Models\Category as FilamentBlogCategory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Category extends FilamentBlogCategory
{
    public static function getForm()
    {
        return [
            TextInput::make('name')
                ->label('Название')
                ->live(true)
                ->afterStateUpdated(function (Get $get, Set $set, ?string $operation, ?string $old, ?string $state) {
                    $set('slug', Str::slug($state));
                })
                ->unique(config('filamentblog.tables.prefix') . 'categories', 'name', null, 'id')
                ->required()
                ->maxLength(155),

            TextInput::make('slug')
                ->label('Ссылка')
                ->unique(config('filamentblog.tables.prefix') . 'categories', 'slug', null, 'id')
                ->readOnly()
                ->maxLength(255),
        ];
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, config('filamentblog.tables.prefix').'category_'.config('filamentblog.tables.prefix').'post');
    }
}
