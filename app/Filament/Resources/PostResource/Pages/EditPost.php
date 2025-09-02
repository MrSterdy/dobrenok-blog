<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Firefly\FilamentBlog\Enums\PostStatus;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected static ?string $title = 'Редактирование';
    protected static ?string $navigationLabel = 'Редактирование';

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($data['status'] === PostStatus::PUBLISHED->value && !$this->record->published_at) {
            $data['published_at'] = now();
        }

        return $data;
    }
}
