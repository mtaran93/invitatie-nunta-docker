<?php

namespace App\Filament\Resources\WrittenGuests;

use App\Filament\Resources\WrittenGuests\Pages\CreateWrittenGuests;
use App\Filament\Resources\WrittenGuests\Pages\EditWrittenGuests;
use App\Filament\Resources\WrittenGuests\Pages\ListWrittenGuests;
use App\Filament\Resources\WrittenGuests\Pages\ViewWrittenGuests;
use App\Filament\Resources\WrittenGuests\Schemas\WrittenGuestsForm;
use App\Filament\Resources\WrittenGuests\Schemas\WrittenGuestsInfolist;
use App\Filament\Resources\WrittenGuests\Tables\WrittenGuestsTable;
use App\Models\WrittenGuest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WrittenGuestsResource extends Resource
{
    protected static ?string $model = WrittenGuest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Written Guests';

    public static function form(Schema $schema): Schema
    {
        return WrittenGuestsForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return WrittenGuestsInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WrittenGuestsTable::configure($table);
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
            'index' => ListWrittenGuests::route('/'),
            'create' => CreateWrittenGuests::route('/create'),
            'view' => ViewWrittenGuests::route('/{record}'),
            'edit' => EditWrittenGuests::route('/{record}/edit'),
        ];
    }
}
