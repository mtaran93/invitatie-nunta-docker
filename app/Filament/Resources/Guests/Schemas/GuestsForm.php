<?php

namespace App\Filament\Resources\Guests\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Schema;

class GuestsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required(),
                ToggleButtons::make('needs_accommodation')->label('Needs Accommodation')->inline(),
                ToggleButtons::make('needs_transportation')->label('Needs Transportation')->inline(),
            ]);
    }
}
