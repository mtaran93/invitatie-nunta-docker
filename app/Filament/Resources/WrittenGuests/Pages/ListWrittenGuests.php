<?php

namespace App\Filament\Resources\WrittenGuests\Pages;

use App\Filament\Resources\WrittenGuests\WrittenGuestsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWrittenGuests extends ListRecords
{
    protected static string $resource = WrittenGuestsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
