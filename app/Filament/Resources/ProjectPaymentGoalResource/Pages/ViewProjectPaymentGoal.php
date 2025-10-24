<?php

namespace App\Filament\Resources\ProjectPaymentGoalResource\Pages;

use App\Filament\Resources\ProjectPaymentGoalResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProjectPaymentGoal extends ViewRecord
{
    protected static string $resource = ProjectPaymentGoalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
