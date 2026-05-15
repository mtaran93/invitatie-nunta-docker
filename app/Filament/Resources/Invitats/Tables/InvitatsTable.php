<?php

namespace App\Filament\Resources\Invitats\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class InvitatsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name', 'asc')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('person_number')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('kid_number')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('accommodation')
                    ->boolean(),
                IconColumn::make('confirmed')
                    ->boolean(),
                TextColumn::make('weddingTable.id')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('confirmed')
                    ->label('Confirmare')
                    ->trueLabel('Confirmați')
                    ->falseLabel('Neconfirmați')
                    ->placeholder('Toți'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
