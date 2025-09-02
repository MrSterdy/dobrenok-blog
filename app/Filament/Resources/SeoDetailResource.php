<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SeoDetailResource\Pages;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\SeoDetail;

class SeoDetailResource extends Resource
{
    protected static ?string $model = SeoDetail::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-magnifying-glass';
    protected static ?string $navigationGroup = 'Блог';
    protected static ?string $navigationLabel = 'SEO Детали';
    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'SEO Деталь';
    protected static ?string $pluralModelLabel = 'SEO Детали';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form
            ->schema(SeoDetail::getForm());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('post.title')
                    ->label('Пост')
                    ->limit(20),
                Tables\Columns\TextColumn::make('title')
                    ->label('Заголовок')
                    ->limit(20)
                    ->searchable(),
                Tables\Columns\TextColumn::make('keywords')->badge()
                    ->label('Ключевые слова')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Обновлено')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                //
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSeoDetails::route('/'),
            'create' => Pages\CreateSeoDetail::route('/create'),
            'edit' => Pages\EditSeoDetail::route('/{record}/edit'),
        ];
    }
}
