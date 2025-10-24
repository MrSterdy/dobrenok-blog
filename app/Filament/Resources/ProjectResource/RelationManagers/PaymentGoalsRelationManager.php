<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentGoalsRelationManager extends RelationManager
{
    protected static string $relationship = 'paymentGoals';

    protected static ?string $title = 'Цели сбора средств';

    protected static ?string $modelLabel = 'Цель';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('target_amount')
                    ->label('Целевая сумма')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->prefix('₽')
                    ->step(0.01),
                Forms\Components\TextInput::make('current_amount')
                    ->label('Текущая сумма')
                    ->numeric()
                    ->minValue(0)
                    ->prefix('₽')
                    ->step(0.01)
                    ->default(0)
                    ->disabled()
                    ->dehydrated()
                    ->helperText('Автоматически обновляется при поступлении платежей'),
                Forms\Components\Select::make('currency')
                    ->label('Валюта')
                    ->options([
                        'RUB' => 'RUB (₽)',
                        'USD' => 'USD ($)',
                        'EUR' => 'EUR (€)',
                    ])
                    ->default('RUB')
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->label('Описание')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\DatePicker::make('deadline')
                    ->label('Крайний срок')
                    ->native(false)
                    ->displayFormat('d.m.Y')
                    ->minDate(now()),
                Forms\Components\Toggle::make('is_active')
                    ->label('Активна')
                    ->default(true)
                    ->helperText('Только одна цель может быть активной'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('target_amount')
            ->columns([
                Tables\Columns\TextColumn::make('target_amount')
                    ->label('Целевая сумма')
                    ->money('RUB', locale: 'ru')
                    ->sortable(),
                Tables\Columns\TextColumn::make('current_amount')
                    ->label('Текущая сумма')
                    ->money('RUB', locale: 'ru')
                    ->sortable(),
                Tables\Columns\TextColumn::make('progress_percentage')
                    ->label('Прогресс')
                    ->formatStateUsing(fn($state) => number_format($state, 1) . '%')
                    ->color(fn($state) => match (true) {
                        $state >= 100 => 'success',
                        $state >= 75 => 'info',
                        $state >= 50 => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('deadline')
                    ->label('Крайний срок')
                    ->date('d.m.Y')
                    ->sortable()
                    ->color(fn($state) => $state && $state->isPast() ? 'danger' : null),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активна')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создана')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активна')
                    ->placeholder('Все цели')
                    ->trueLabel('Только активные')
                    ->falseLabel('Только неактивные'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Создать цель'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Нет целей сбора средств')
            ->emptyStateDescription('Создайте первую цель для этого проекта')
            ->emptyStateIcon('heroicon-o-currency-dollar');
    }
}
