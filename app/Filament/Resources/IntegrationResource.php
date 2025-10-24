<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IntegrationResource\Pages;
use App\Models\Integration;
use App\Services\SocialMedia\SocialMediaPublisherFactory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class IntegrationResource extends Resource
{
    protected static ?string $model = Integration::class;

    protected static ?string $navigationIcon = 'heroicon-o-share';
    protected static ?string $navigationGroup = 'Настройки';
    protected static ?string $navigationLabel = 'Интеграции с соцсетями';
    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'Интеграция';
    protected static ?string $pluralModelLabel = 'Интеграции';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основные настройки')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Название')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('type')
                            ->label('Тип интеграции')
                            ->options(SocialMediaPublisherFactory::getAvailableTypes())
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($set) {
                                // Сбрасываем вложенные JSON-поля при смене типа
                                $set('credentials', []);
                                $set('settings', []);
                            }),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Активна')
                            ->default(true),
                    ]),

                Forms\Components\Section::make('Учетные данные')
                    ->schema(fn($get) => [
                        // Вкладываем в statePath('credentials'), чтобы корректно собирался JSON
                        Forms\Components\Group::make()
                            ->statePath('credentials')
                            ->schema(self::getCredentialsFields($get('type'))),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Дополнительные настройки')
                    ->schema(fn($get) => [
                        Forms\Components\Group::make()
                            ->statePath('settings')
                            ->schema(self::getSettingsFields($get('type'))),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Тип')
                    ->badge()
                    ->formatStateUsing(fn($state) => SocialMediaPublisherFactory::getAvailableTypes()[$state] ?? $state),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активна')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создана')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Тип')
                    ->options(SocialMediaPublisherFactory::getAvailableTypes()),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активна'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIntegrations::route('/'),
            'create' => Pages\CreateIntegration::route('/create'),
            'edit' => Pages\EditIntegration::route('/{record}/edit'),
        ];
    }

    private static function getCredentialsFields(?string $type): array
    {
        return match ($type) {
            'vk' => [
                Forms\Components\TextInput::make('credentials.access_token')
                    ->label('Access Token')
                    ->required()
                    ->password()
                    ->revealable(),
            ],
            'telegram' => [
                Forms\Components\TextInput::make('credentials.bot_token')
                    ->label('Bot Token')
                    ->required()
                    ->password()
                    ->revealable(),
            ],
            default => [
                Forms\Components\Placeholder::make('select_type')
                    ->label('Выберите тип интеграции')
                    ->content('Сначала выберите тип интеграции'),
            ],
        };
    }

    private static function getSettingsFields(?string $type): array
    {
        return match ($type) {
            'vk' => [
                Forms\Components\TextInput::make('settings.group_id')
                    ->label('ID группы')
                    ->required()
                    ->numeric()
                    ->helperText('ID группы ВКонтакте (без минуса)'),
            ],
            'telegram' => [
                Forms\Components\TextInput::make('settings.channel_id')
                    ->label('ID канала')
                    ->required()
                    ->helperText('ID канала в Telegram (например: @channelname или -1001234567890)'),
                Forms\Components\TextInput::make('settings.channel_username')
                    ->label('Username канала')
                    ->helperText('Username канала без @ (для формирования ссылок)'),
            ],
            default => [],
        };
    }
}
