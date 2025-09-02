<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use Filament\Resources\Pages\CreateRecord;
use App\Enums\PostStatus;
use Illuminate\Support\Facades\Auth;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();

        if ($data['status'] === PostStatus::PUBLISHED->value) {
            $data['published_at'] = now();
        }

        return $data;
    }
}
