<?php

namespace App\Filament\Resources\ProjectPaymentGoalResource\Pages;

use App\Filament\Resources\ProjectPaymentGoalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProjectPaymentGoal extends EditRecord
{
    protected static string $resource = ProjectPaymentGoalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
