<?php

namespace App\Filament\Resources\PostPublicationResource\Pages;

use App\Filament\Resources\PostPublicationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePostPublication extends CreateRecord
{
    protected static string $resource = PostPublicationResource::class;
}
