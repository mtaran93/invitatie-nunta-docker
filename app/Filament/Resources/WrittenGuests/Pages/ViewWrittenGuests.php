<?php

namespace App\Filament\Resources\WrittenGuests\Pages;

use App\Filament\Resources\WrittenGuests\WrittenGuestsResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewWrittenGuests extends ViewRecord
{
    protected static string $resource = WrittenGuestsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
