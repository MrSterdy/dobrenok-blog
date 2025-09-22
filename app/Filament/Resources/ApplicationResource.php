<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApplicationResource\Pages;
use App\Models\Application;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox';
    protected static ?string $navigationGroup = 'Проекты';
    protected static ?string $navigationLabel = 'Заявки';
    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'Заявка';
    protected static ?string $pluralModelLabel = 'Заявки';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Applications are created via API; admin is read-only here
                Forms\Components\TextInput::make('name')
                    ->label('Название заявки')
                    ->disabled(),
                Forms\Components\Select::make('project_id')
                    ->label('Проект')
                    ->relationship('project', 'name')
                    ->disabled(),
                Forms\Components\KeyValue::make('data')
                    ->label('Данные заявки')
                    ->formatStateUsing(function ($state) {
                        if (!is_array($state)) {
                            return [];
                        }

                        $labels = [
                            'name' => 'Имя',
                            'phone' => 'Телефон',
                            'age' => 'Возраст',
                            'availability' => 'Доступность',
                            'hasCar' => 'Есть машина?',
                            'work' => 'Работа'
                        ];

                        $formatted = [];
                        foreach ($state as $key => $value) {
                            $displayKey = $labels[$key] ?? ucfirst(str_replace('_', ' ', $key));
                            $formatted[$displayKey] = is_bool($value)
                                ? ($value === true ? 'Да' : 'Нет')
                                : (is_array($value) ? implode(', ', $value) : $value);
                        }

                        return $formatted;
                    })
                    ->columnSpanFull()
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Название')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('project.name')->label('Проект')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Создано')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('project_id')->label('Проект')->relationship('project', 'name'),
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
            'index' => Pages\ListApplications::route('/'),
            'view' => Pages\ViewApplication::route('/{record}'),
        ];
    }
}
