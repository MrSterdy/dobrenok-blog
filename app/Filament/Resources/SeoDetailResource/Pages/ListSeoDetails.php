<?php

namespace App\Filament\Resources\SeoDetailResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\SeoDetailResource;

class ListSeoDetails extends ListRecords
{
    protected static string $resource = SeoDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
