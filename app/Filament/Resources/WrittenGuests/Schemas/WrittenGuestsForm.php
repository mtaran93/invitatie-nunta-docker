<?php

namespace App\Filament\Resources\WrittenGuests\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Schema;

class WrittenGuestsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Nume')->required(),
                ToggleButtons::make('answer')->label('Raspuns')
                    ->boolean()
                    ->grouped(),
                TextInput::make('persons')->label('Adulti')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(2)
                    ->required(),
                ToggleButtons::make('children')->label('Copii')
                    ->boolean()
                    ->grouped(),
                TextInput::make('menu_1')->label('Menu 1')->required(),
                TextInput::make('menu_1')->label('Menu 2'),
            ]);
    }
}
