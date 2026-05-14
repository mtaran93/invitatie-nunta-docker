<?php

namespace App\Filament\Resources\UploadedPhotos\Pages;

use App\Filament\Resources\UploadedPhotos\UploadedPhotoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

class ViewUploadedPhoto extends ViewRecord
{
    protected static string $resource = UploadedPhotoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
