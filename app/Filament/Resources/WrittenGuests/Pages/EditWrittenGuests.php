<?php

namespace App\Filament\Resources\WrittenGuests\Pages;

use App\Filament\Resources\WrittenGuests\WrittenGuestsResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditWrittenGuests extends EditRecord
{
    protected static string $resource = WrittenGuestsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
