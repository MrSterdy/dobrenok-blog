<?php

namespace App\Models;

use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Set;
use FilamentTiptapEditor\TiptapEditor;
use App\Enums\PostStatus;
use Firefly\FilamentBlog\Models\Category;
use Firefly\FilamentBlog\Models\Post as FilamentBlogPost;
use Firefly\FilamentBlog\Models\Tag;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Post extends FilamentBlogPost
{
    public function getFillable()
    {
        return array_merge(parent::getFillable(), [
            'project_id',
        ]);
    }

    public function getCasts()
    {
        return array_merge(parent::getCasts(), [
            'status' => PostStatus::class,
        ]);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function publications(): HasMany
    {
        return $this->hasMany(PostPublication::class);
    }

    /**
     * Получить публикации в определенной соцсети
     */
    public function getPublicationForIntegration(int $integrationId): ?PostPublication
    {
        return $this->publications()->where('integration_id', $integrationId)->first();
    }

    /**
     * Проверить, был ли пост опубликован в определенной соцсети
     */
    public function isPublishedTo(int $integrationId): bool
    {
        return $this->publications()
            ->where('integration_id', $integrationId)
            ->where('status', 'published')
            ->exists();
    }

    public static function getForm()
    {
        return [
            Section::make('Основная информация поста')
                ->schema([
                    Fieldset::make('Заголовки поста')
                        ->schema([
                            Select::make('project_id')
                                ->label('Проект')
                                ->relationship('project', 'name')
                                ->searchable()
                                ->preload()
                                ->columnSpanFull()
                                ->required(),

                            Select::make('category_id')
                                ->label('Категории')
                                ->multiple()
                                ->preload()
                                ->createOptionForm(Category::getForm())
                                ->searchable()
                                ->relationship('categories', 'name')
                                ->columnSpanFull(),

                            TextInput::make('title')
                                ->label('Заголовок')
                                ->live(true)
                                ->afterStateUpdated(fn(Set $set, ?string $state) => $set(
                                    'slug',
                                    Str::slug($state)
                                ))
                                ->required()
                                ->unique(config('filamentblog.tables.prefix') . 'posts', 'title', null, 'id')
                                ->maxLength(255),

                            TextInput::make('slug')
                                ->label('Ссылка')
                                ->maxLength(255),

                            Textarea::make('sub_title')
                                ->label('Описание')
                                ->maxLength(255)
                                ->columnSpanFull(),

                            Select::make('tag_id')
                                ->label('Теги')
                                ->multiple()
                                ->preload()
                                ->createOptionForm(Tag::getForm())
                                ->searchable()
                                ->relationship('tags', 'name')
                                ->columnSpanFull(),
                        ]),
                    TiptapEditor::make('body')
                        ->label('Текст')
                        ->profile('default')
                        ->disableFloatingMenus()
                        ->extraInputAttributes(['style' => 'max-height: 30rem; min-height: 24rem'])
                        ->required()
                        ->columnSpanFull(),
                    Fieldset::make('Обложка поста')
                        ->schema([
                            FileUpload::make('cover_photo_path')
                                ->label('Обложка')
                                ->disk('public')
                                ->directory('blog-feature-images')
                                ->visibility('public')
                                ->hint('Это изображение используется в посте в качестве обложки. Рекомендуемый размер изображения 1200x628')
                                ->image()
                                ->preserveFilenames()
                                ->imageEditor()
                                ->maxSize(1024 * 5)
                                ->required(),
                            TextInput::make('photo_alt_text')
                                ->label('Описание изображения')
                                ->required(),
                        ])->columns(1),

                    Fieldset::make('Статус поста')
                        ->schema([
                            ToggleButtons::make('status')
                                ->label('Статус')
                                ->live()
                                ->inline()
                                ->options(PostStatus::class)
                                ->required(),

                            DateTimePicker::make('scheduled_for')
                                ->visible(function ($get) {
                                    return $get('status') === PostStatus::SCHEDULED->value;
                                })
                                ->required(function ($get) {
                                    return $get('status') === PostStatus::SCHEDULED->value;
                                })
                                ->minDate(now()->addMinutes(5))
                                ->native(false),
                        ]),
                    Select::make(config('filamentblog.user.foreign_key'))
                        ->label('Автор')
                        ->relationship('user', 'name')
                        ->nullable(false)
                        ->default(Auth::id()),
                ]),

            Section::make('Публикация в социальных сетях')
                ->schema(function () {
                    // Получаем все активные интеграции
                    $integrations = \App\Models\Integration::where('is_active', true)->get();

                    if ($integrations->isEmpty()) {
                        return [
                            Forms\Components\Placeholder::make('no_integrations')
                                ->label('Нет активных интеграций')
                                ->content('Настройте интеграции с социальными сетями в разделе "Настройки"'),
                        ];
                    }

                    // Динамически создаем toggle для каждой активной интеграции
                    $toggles = [];
                    foreach ($integrations as $integration) {
                        $toggles[] = Forms\Components\Toggle::make("publish_to_{$integration->type}")
                            ->label("Опубликовать в {$integration->name}")
                            ->default(true)
                            ->inline(false)
                            ->helperText("Пост будет автоматически опубликован: {$integration->name}");
                    }

                    return [
                        Fieldset::make('Выберите социальные сети для публикации')
                            ->schema($toggles)
                            ->columns(2),
                    ];
                })
                ->description('Публикация произойдет автоматически при смене статуса на "Опубликовано"')
                ->collapsible(),
        ];
    }
}
