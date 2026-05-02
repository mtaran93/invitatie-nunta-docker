<?php

namespace App\Filament\Resources\Invitats\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class InvitatForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('person_number')
                    ->required()
                    ->numeric(),
                TextInput::make('kid_number')
                    ->required()
                    ->numeric(),
                Toggle::make('accommodation')
                    ->required(),
                Toggle::make('confirmed')
                    ->required(),
                Select::make('wedding_table_id')
                    ->relationship('weddingTable', 'id'),
            ]);
    }
}
