<?php

namespace App\Filament\Resources\PostPublicationResource\Pages;

use App\Filament\Resources\PostPublicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPostPublication extends ViewRecord
{
    protected static string $resource = PostPublicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
