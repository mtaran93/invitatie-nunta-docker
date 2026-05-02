<?php

namespace App\Filament\Resources\Invitats\Pages;

use App\Filament\Resources\Invitats\InvitatResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInvitat extends EditRecord
{
    protected static string $resource = InvitatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
