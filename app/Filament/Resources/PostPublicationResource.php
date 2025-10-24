<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostPublicationResource\Pages;
use App\Models\PostPublication;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PostPublicationResource extends Resource
{
    protected static ?string $model = PostPublication::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
    protected static ?string $navigationGroup = 'Блог';
    protected static ?string $navigationLabel = 'Публикации в соцсетях';
    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'Публикация';
    protected static ?string $pluralModelLabel = 'Публикации в соцсетях';

    public static function getNavigationBadge(): ?string
    {
        $pending = PostPublication::where('status', 'pending')->count();
        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('post.title')
                    ->label('Пост')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->url(fn($record) => $record->post ? route('filamentblog.post.show', $record->post->slug) : null)
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('integration.name')
                    ->label('Социальная сеть')
                    ->badge()
                    ->color(fn($record) => match ($record->integration->type) {
                        'vk' => 'primary',
                        'telegram' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'published' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn($state) => match ($state) {
                        'published' => 'Опубликовано',
                        'pending' => 'Ожидает',
                        'failed' => 'Ошибка',
                        default => $state,
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('external_url')
                    ->label('Ссылка')
                    ->limit(40)
                    ->url(fn($record) => $record->external_url)
                    ->openUrlInNewTab()
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Дата публикации')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('retry_count')
                    ->label('Попыток')
                    ->badge()
                    ->color(fn($state) => $state > 0 ? 'warning' : 'gray')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'published' => 'Опубликовано',
                        'pending' => 'Ожидает',
                        'failed' => 'Ошибка',
                    ]),
                Tables\Filters\SelectFilter::make('integration')
                    ->label('Социальная сеть')
                    ->relationship('integration', 'name'),
                Tables\Filters\SelectFilter::make('post')
                    ->label('Пост')
                    ->relationship('post', 'title')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('republish')
                        ->label('Опубликовать снова')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->visible(fn($record) => $record->status === 'failed')
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $record->update([
                                'status' => 'pending',
                                'error_message' => null,
                            ]);

                            \App\Jobs\PublishPostToSocialMedia::dispatch(
                                $record->post,
                                $record->integration
                            )->onQueue('social-media');

                            \Filament\Notifications\Notification::make()
                                ->title('Публикация поставлена в очередь')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('republish_failed')
                        ->label('Опубликовать снова')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $count = 0;
                            foreach ($records as $record) {
                                if ($record->status === 'failed') {
                                    $record->update([
                                        'status' => 'pending',
                                        'error_message' => null,
                                    ]);

                                    \App\Jobs\PublishPostToSocialMedia::dispatch(
                                        $record->post,
                                        $record->integration
                                    )->onQueue('social-media');

                                    $count++;
                                }
                            }

                            \Filament\Notifications\Notification::make()
                                ->title("Поставлено в очередь: {$count}")
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Информация о публикации')
                    ->schema([
                        Forms\Components\Select::make('post_id')
                            ->label('Пост')
                            ->relationship('post', 'title')
                            ->searchable()
                            ->required()
                            ->disabled(),
                        Forms\Components\Select::make('integration_id')
                            ->label('Социальная сеть')
                            ->relationship('integration', 'name')
                            ->required()
                            ->disabled(),
                        Forms\Components\Select::make('status')
                            ->label('Статус')
                            ->options([
                                'published' => 'Опубликовано',
                                'pending' => 'Ожидает',
                                'failed' => 'Ошибка',
                            ])
                            ->required()
                            ->disabled(),
                    ]),
                Forms\Components\Section::make('Детали публикации')
                    ->schema([
                        Forms\Components\TextInput::make('external_id')
                            ->label('ID в соцсети')
                            ->disabled(),
                        Forms\Components\TextInput::make('external_url')
                            ->label('Ссылка на публикацию')
                            ->url()
                            ->disabled()
                            ->columnSpanFull(),
                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Дата публикации')
                            ->disabled(),
                        Forms\Components\TextInput::make('retry_count')
                            ->label('Количество попыток')
                            ->numeric()
                            ->disabled(),
                    ])->columns(2),
                Forms\Components\Section::make('Ошибки')
                    ->schema([
                        Forms\Components\Textarea::make('error_message')
                            ->label('Сообщение об ошибке')
                            ->rows(3)
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->visible(fn($record) => $record && $record->error_message !== null),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPostPublications::route('/'),
            'view' => Pages\ViewPostPublication::route('/{record}'),
        ];
    }
}
