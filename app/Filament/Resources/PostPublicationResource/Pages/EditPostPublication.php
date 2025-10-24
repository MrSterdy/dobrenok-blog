<?php

namespace App\Filament\Resources\PostPublicationResource\Pages;

use App\Filament\Resources\PostPublicationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPostPublication extends EditRecord
{
    protected static string $resource = PostPublicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
