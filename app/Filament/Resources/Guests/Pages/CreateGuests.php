<?php

namespace App\Filament\Resources\Guests\Pages;

use App\Filament\Resources\Guests\GuestsResource;
use Filament\Resources\Pages\CreateRecord;
use Ramsey\Uuid\Uuid;

class CreateGuests extends CreateRecord
{
    protected static string $resource = GuestsResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $uuid = Uuid::uuid4()->toString();
        $data['uuid'] = $uuid;

        return $data;
    }
}
