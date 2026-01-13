<?php

namespace App\Filament\Resources\Guests;

use App\Filament\Resources\Guests\Pages\CreateGuests;
use App\Filament\Resources\Guests\Pages\EditGuests;
use App\Filament\Resources\Guests\Pages\ListGuests;
use App\Filament\Resources\Guests\Pages\ViewGuests;
use App\Filament\Resources\Guests\Schemas\GuestsForm;
use App\Filament\Resources\Guests\Schemas\GuestsInfolist;
use App\Filament\Resources\Guests\Tables\GuestsTable;
use App\Models\Guest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GuestsResource extends Resource
{
    protected static ?string $model = Guest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Guests';

    public static function form(Schema $schema): Schema
    {
        return GuestsForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return GuestsInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GuestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGuests::route('/'),
            'create' => CreateGuests::route('/create'),
            'view' => ViewGuests::route('/{record}'),
            'edit' => EditGuests::route('/{record}/edit'),
        ];
    }
}
