<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationGroup = 'Проекты';
    protected static ?string $navigationLabel = 'Мероприятия';
    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'Мероприятие';
    protected static ?string $pluralModelLabel = 'Мероприятия';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Мероприятие')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Название')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('slug')
                            ->label('Ссылка')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('project_id')
                            ->label('Проект')
                            ->relationship('project', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\DateTimePicker::make('start_date')
                            ->label('Дата начала')
                            ->native(false)
                            ->required(),

                        Forms\Components\FileUpload::make('cover_photo_path')
                            ->label('Обложка')
                            ->disk('public')
                            ->directory('event-images')
                            ->visibility('public')
                            ->image()
                            ->preserveFilenames()
                            ->imageEditor()
                            ->maxSize(1024 * 5)
                            ->required(),

                        Forms\Components\Textarea::make('short_description')
                            ->label('Краткое описание')
                            ->rows(3)
                            ->required(),

                        Forms\Components\RichEditor::make('body')
                            ->label('Описание')
                            ->columnSpanFull()
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_photo_path')
                    ->label('Обложка')
                    ->disk('public')
                    ->square(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('project.name')
                    ->label('Проект')
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Начало')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Проект')
                    ->relationship('project', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}
