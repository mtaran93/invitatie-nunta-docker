<?php

namespace App\Filament\Resources\UploadedPhotos\Tables;

use App\Models\UploadedPhoto;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UploadedPhotosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('preview')
                    ->label('Poza')
                    ->getStateUsing(fn (UploadedPhoto $record) => route('admin.photos.thumb', ['photo' => $record->id]))
                    ->height(80)
                    ->square(),
                TextColumn::make('original_name')->label('Nume')->searchable()->limit(40),
                TextColumn::make('mime')->label('Tip'),
                TextColumn::make('size')
                    ->label('Marime')
                    ->formatStateUsing(fn ($state) => number_format(((int) $state) / 1024, 1).' KB')
                    ->sortable(),
                TextColumn::make('width')->label('W'),
                TextColumn::make('height')->label('H'),
                TextColumn::make('status')
                    ->label('Stare')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    })
                    ->sortable(),
                TextColumn::make('created_at')->label('Incarcata la')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Stare')
                    ->options([
                        'pending' => 'In asteptare',
                        'approved' => 'Aprobata',
                        'rejected' => 'Respinsa',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
