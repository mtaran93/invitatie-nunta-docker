<?php

namespace App\Filament\Resources\Invitats;

use App\Filament\Resources\Invitats\Pages\CreateInvitat;
use App\Filament\Resources\Invitats\Pages\EditInvitat;
use App\Filament\Resources\Invitats\Pages\ListInvitats;
use App\Filament\Resources\Invitats\Schemas\InvitatForm;
use App\Filament\Resources\Invitats\Tables\InvitatsTable;
use App\Models\Invitat;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InvitatResource extends Resource
{
    protected static ?string $model = Invitat::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Invitati Mese';

    protected static ?string $pluralModelLabel = 'Invitati Mese';

    protected static ?string $modelLabel = 'Invitat Mese';

    public static function form(Schema $schema): Schema
    {
        return InvitatForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InvitatsTable::configure($table);
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
            'index' => ListInvitats::route('/'),
            'create' => CreateInvitat::route('/create'),
            'edit' => EditInvitat::route('/{record}/edit'),
        ];
    }
}
