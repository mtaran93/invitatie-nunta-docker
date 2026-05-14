<?php

namespace App\Filament\Resources\UploadedPhotos\Schemas;

use App\Models\UploadedPhoto;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UploadedPhotoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ImageEntry::make('preview')
                    ->label('Poza')
                    ->getStateUsing(fn (UploadedPhoto $record) => route('admin.photos.file', ['photo' => $record->id]))
                    ->height(480),
                TextEntry::make('original_name')->label('Nume original'),
                TextEntry::make('mime')->label('Tip'),
                TextEntry::make('size')
                    ->label('Marime')
                    ->formatStateUsing(fn ($state) => number_format(((int) $state) / 1024, 1).' KB'),
                TextEntry::make('width')->label('Latime'),
                TextEntry::make('height')->label('Inaltime'),
                TextEntry::make('status')->label('Stare')->badge(),
                TextEntry::make('ip')->label('IP'),
                TextEntry::make('user_agent')->label('User agent'),
                TextEntry::make('created_at')->label('Incarcata la')->dateTime(),
            ]);
    }
}
