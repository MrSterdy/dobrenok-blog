<?php

namespace App\Filament\Resources\TagResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\TagResource;

class ListTags extends ListRecords
{
    protected static string $resource = TagResource::class;

    protected static ?string $title = 'Теги';
    protected static ?string $navigationLabel = 'Теги';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
