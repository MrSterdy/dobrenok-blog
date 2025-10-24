<?php

namespace App\Filament\Resources\PostResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PublicationsRelationManager extends RelationManager
{
    protected static string $relationship = 'publications';

    protected static ?string $title = 'Публикации в соцсетях';
    protected static ?string $modelLabel = 'Публикация';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('external_url')
            ->columns([
                Tables\Columns\TextColumn::make('integration.name')
                    ->label('Социальная сеть')
                    ->badge()
                    ->color(fn($record) => match ($record->integration->type) {
                        'vk' => 'primary',
                        'telegram' => 'info',
                        default => 'gray',
                    }),
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
                    }),
                Tables\Columns\TextColumn::make('external_url')
                    ->label('Ссылка')
                    ->limit(50)
                    ->url(fn($record) => $record->external_url)
                    ->openUrlInNewTab()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Дата публикации')
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('retry_count')
                    ->label('Попыток')
                    ->badge()
                    ->color(fn($state) => $state > 0 ? 'warning' : 'gray'),
                Tables\Columns\TextColumn::make('error_message')
                    ->label('Ошибка')
                    ->limit(50)
                    ->tooltip(fn($record) => $record->error_message)
                    ->placeholder('—')
                    ->toggleable(),
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
            ])
            ->actions([
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
                Tables\Actions\ViewAction::make()
                    ->form([
                        Forms\Components\TextInput::make('integration.name')
                            ->label('Социальная сеть')
                            ->disabled(),
                        Forms\Components\TextInput::make('status')
                            ->label('Статус')
                            ->disabled()
                            ->formatStateUsing(fn($state) => match ($state) {
                                'published' => 'Опубликовано',
                                'pending' => 'Ожидает',
                                'failed' => 'Ошибка',
                                default => $state,
                            }),
                        Forms\Components\TextInput::make('external_id')
                            ->label('ID в соцсети')
                            ->disabled(),
                        Forms\Components\TextInput::make('external_url')
                            ->label('Ссылка')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Дата публикации')
                            ->disabled(),
                        Forms\Components\TextInput::make('retry_count')
                            ->label('Количество попыток')
                            ->disabled(),
                        Forms\Components\Textarea::make('error_message')
                            ->label('Сообщение об ошибке')
                            ->disabled()
                            ->rows(3)
                            ->visible(fn($record) => $record->error_message !== null),
                    ]),
                Tables\Actions\DeleteAction::make()
                    ->label('Удалить запись'),
            ])
            ->emptyStateHeading('Публикации отсутствуют')
            ->emptyStateDescription('Пост еще не был опубликован в социальных сетях')
            ->emptyStateIcon('heroicon-o-share');
    }
}
