<?php

namespace App\Filament\Resources\UploadedPhotos\Pages;

use App\Filament\Resources\UploadedPhotos\UploadedPhotoResource;
use Filament\Resources\Pages\ListRecords;

class ListUploadedPhotos extends ListRecords
{
    protected static string $resource = UploadedPhotoResource::class;
}
