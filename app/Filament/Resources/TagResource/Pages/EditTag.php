<?php

namespace App\Filament\Resources\TagResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\TagResource;

class EditTag extends EditRecord
{
    protected static string $resource = TagResource::class;

    protected static ?string $title = 'Редактирование';
    protected static ?string $navigationLabel = 'Редактирование';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
