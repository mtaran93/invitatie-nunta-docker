<?php

namespace App\Filament\Imports;

use App\Models\Invitat;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class InvitatImporter extends Importer
{
    protected static ?string $model = Invitat::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('person_number')
                ->castStateUsing(fn (?string $state): int => (int) trim((string) $state))
                ->rules(['integer', 'min:0']),
            ImportColumn::make('kid_number')
                ->castStateUsing(fn (?string $state): int => (int) trim((string) $state))
                ->rules(['integer', 'min:0']),
            ImportColumn::make('accommodation')
                ->castStateUsing(fn (?string $state): bool => self::parseRoBool($state)),
            ImportColumn::make('confirmed')
                ->castStateUsing(fn (?string $state): bool => self::parseRoBool($state)),
        ];
    }

    public function resolveRecord(): Invitat
    {
        return new Invitat();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your invitat import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }

    private static function parseRoBool(?string $state): bool
    {
        return in_array(
            strtolower(trim((string) $state)),
            ['da', 'yes', 'y', 'true', '1'],
            true,
        );
    }
}
