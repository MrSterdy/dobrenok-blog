<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables;
use Filament\Tables\Table;

class ManagePostSeoDetail extends ManageRelatedRecords
{
    protected static string $resource = PostResource::class;

    protected static string $relationship = 'seoDetail';

    protected static ?string $title = 'SEO Детали';

    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass';
    protected static ?string $nvaigationLabel = 'SEO Детали';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Заголовок')
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('Описание')
                    ->maxLength(255),
                Forms\Components\TextInput::make('keywords')
                    ->label('Ключевые слова')
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Заголовок'),
                Tables\Columns\TextColumn::make('description')
                    ->label('Описание')
                    ->limit(50),
                Tables\Columns\TextColumn::make('keywords')
                    ->label('Ключевые слова')
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
