<?php

namespace App\Filament\Resources\Invitats\Pages;

use App\Filament\Imports\InvitatImporter;
use App\Filament\Resources\Invitats\InvitatResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;

class ListInvitats extends ListRecords
{
    protected static string $resource = InvitatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->importer(InvitatImporter::class),
            CreateAction::make(),
        ];
    }
}
