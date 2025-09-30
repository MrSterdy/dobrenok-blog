<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Проекты';
    protected static ?string $navigationLabel = 'Платежи';
    protected static ?int $navigationSort = 6;

    protected static ?string $modelLabel = 'Платеж';
    protected static ?string $pluralModelLabel = 'Платежи';

    protected static ?string $recordTitleAttribute = 'external_payment_id';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('external_payment_id')
                    ->label('ID платежа')
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
                Forms\Components\TextInput::make('status')
                    ->label('Статус')
                    ->disabled(),
                Forms\Components\DateTimePicker::make('finished_at')
                    ->label('Завершен')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('external_payment_id')
                    ->label('ID платежа')
                    ->searchable()
                    ->sortable(),
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
                Tables\Columns\TextColumn::make('finished_at')
                    ->label('Завершен')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
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
                        'pending' => 'В ожидании',
                        'completed' => 'Завершен',
                        'failed' => 'Неудачный',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListPayments::route('/'),
            'view' => Pages\ViewPayment::route('/{record}'),
        ];
    }
}
