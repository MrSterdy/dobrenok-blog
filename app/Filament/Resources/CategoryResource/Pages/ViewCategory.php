<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\CategoryResource;
use Firefly\FilamentBlog\Models\Category;

class ViewCategory extends ViewRecord
{
    protected static string $resource = CategoryResource::class;

    public function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->slideOver()
                ->form(Category::getForm()),
        ];
    }
}
