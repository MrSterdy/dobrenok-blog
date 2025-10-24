<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectPaymentGoalResource\Pages;
use App\Filament\Resources\ProjectPaymentGoalResource\RelationManagers;
use App\Models\ProjectPaymentGoal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProjectPaymentGoalResource extends Resource
{
    protected static ?string $model = ProjectPaymentGoal::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Проекты';
    protected static ?string $navigationLabel = 'Цели сбора средств';
    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Цель сбора средств';
    protected static ?string $pluralModelLabel = 'Цели сбора средств';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Информация о проекте')
                    ->schema([
                        Forms\Components\Select::make('project_id')
                            ->label('Проект')
                            ->relationship('project', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Финансовая информация')
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
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('₽')
                            ->step(0.01)
                            ->default(0)
                            ->helperText('Автоматически обновляется при поступлении платежей'),
                        Forms\Components\Select::make('currency')
                            ->label('Валюта')
                            ->options([
                                'RUB' => 'RUB (₽)',
                            ])
                            ->default('RUB')
                            ->required(),
                    ])->columns(3),

                Forms\Components\Section::make('Дополнительная информация')
                    ->schema([
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
                            ->required()
                            ->helperText('Только одна цель может быть активной для проекта'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('project.name')
                    ->label('Проект')
                    ->searchable()
                    ->sortable(),
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
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('remaining_amount')
                    ->label('Осталось')
                    ->money('RUB', locale: 'ru')
                    ->toggleable(),
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
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Обновлена')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Проект')
                    ->relationship('project', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активна')
                    ->placeholder('Все цели')
                    ->trueLabel('Только активные')
                    ->falseLabel('Только неактивные'),
                Tables\Filters\Filter::make('goal_reached')
                    ->label('Цель достигнута')
                    ->query(fn(Builder $query) => $query->whereRaw('current_amount >= target_amount')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjectPaymentGoals::route('/'),
            'create' => Pages\CreateProjectPaymentGoal::route('/create'),
            'view' => Pages\ViewProjectPaymentGoal::route('/{record}'),
            'edit' => Pages\EditProjectPaymentGoal::route('/{record}/edit'),
        ];
    }
}
