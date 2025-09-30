<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionResource\Pages;
use App\Models\Subscription;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?string $navigationGroup = 'Проекты';
    protected static ?string $navigationLabel = 'Подписки';
    protected static ?int $navigationSort = 7;

    protected static ?string $modelLabel = 'Подписка';
    protected static ?string $pluralModelLabel = 'Подписки';

    protected static ?string $recordTitleAttribute = 'email';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->disabled(),
                Forms\Components\TextInput::make('name')
                    ->label('Имя')
                    ->disabled(),
                Forms\Components\Select::make('project_id')
                    ->label('Проект')
                    ->relationship('project', 'name')
                    ->disabled(),
                Forms\Components\TextInput::make('amount')
                    ->label('Сумма')
                    ->numeric()
                    ->disabled(),
                Forms\Components\TextInput::make('currency')
                    ->label('Валюта')
                    ->disabled(),
                Forms\Components\Select::make('status')
                    ->label('Статус')
                    ->options([
                        'active' => 'Активная',
                        'cancelled' => 'Отменена',
                        'expired' => 'Истекла',
                    ])
                    ->disabled(),
                Forms\Components\TextInput::make('external_subscription_id')
                    ->label('ID подписки')
                    ->disabled(),
                Forms\Components\DateTimePicker::make('next_billing_date')
                    ->label('Следующий платеж')
                    ->disabled(),
                Forms\Components\DateTimePicker::make('cancelled_at')
                    ->label('Отменена')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Имя')
                    ->searchable(),
                Tables\Columns\TextColumn::make('project.name')
                    ->label('Проект')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Сумма')
                    ->money('RUB')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn($state) => $state?->getColor() ?? 'gray')
                    ->formatStateUsing(fn($state) => $state?->getDisplayName() ?? $state),
                Tables\Columns\TextColumn::make('next_billing_date')
                    ->label('След. платеж')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создана')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Проект')
                    ->relationship('project', 'name'),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'active' => 'Активная',
                        'cancelled' => 'Отменена',
                        'expired' => 'Истекла',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('cancel')
                    ->label('Отменить')
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn(Subscription $record) => $record->isActive())
                    ->action(fn(Subscription $record) => $record->cancel()),
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
            'index' => Pages\ListSubscriptions::route('/'),
            'view' => Pages\ViewSubscription::route('/{record}'),
        ];
    }
}
