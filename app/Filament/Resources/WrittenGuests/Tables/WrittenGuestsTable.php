<?php

namespace App\Filament\Resources\WrittenGuests\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WrittenGuestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nume')->sortable()->searchable(),
                IconColumn::make('answer')->label('Raspuns')->boolean()->sortable(),
                TextColumn::make('persons')->label('Adulti')->sortable(),
                IconColumn::make('children')->label('Copii')->boolean()->sortable(),
                TextColumn::make('menu_1')->label('Menu 1'),
                TextColumn::make('menu_2')->label('Menu 2'),
                TextColumn::make('created_at')->dateTime()->label('Data confirmarii')->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
