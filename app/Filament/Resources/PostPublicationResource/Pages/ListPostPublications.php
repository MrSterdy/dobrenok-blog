<?php

namespace App\Filament\Resources\PostPublicationResource\Pages;

use App\Filament\Resources\PostPublicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPostPublications extends ListRecords
{
    protected static string $resource = PostPublicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
