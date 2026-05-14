<?php

namespace App\Filament\Resources\UploadedPhotos;

use App\Filament\Resources\UploadedPhotos\Pages\ListUploadedPhotos;
use App\Filament\Resources\UploadedPhotos\Pages\ViewUploadedPhoto;
use App\Filament\Resources\UploadedPhotos\Schemas\UploadedPhotoInfolist;
use App\Filament\Resources\UploadedPhotos\Tables\UploadedPhotosTable;
use App\Models\UploadedPhoto;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UploadedPhotoResource extends Resource
{
    protected static ?string $model = UploadedPhoto::class;

    protected static ?string $navigationLabel = 'Poze';
    protected static ?string $modelLabel = 'Poza';
    protected static ?string $pluralModelLabel = 'Poze';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $recordTitleAttribute = 'original_name';

    public static function infolist(Schema $schema): Schema
    {
        return UploadedPhotoInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UploadedPhotosTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUploadedPhotos::route('/'),
            'view' => ViewUploadedPhoto::route('/{record}'),
        ];
    }
}
