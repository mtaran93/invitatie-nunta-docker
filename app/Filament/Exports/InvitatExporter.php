<?php

namespace App\Filament\Exports;

use App\Models\Invitat;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class InvitatExporter extends Exporter
{
    protected static ?string $model = Invitat::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name')
                ->label('Nume'),
            ExportColumn::make('person_number')
                ->label('Adulți'),
            ExportColumn::make('kid_number')
                ->label('Copii'),
            ExportColumn::make('accommodation')
                ->label('Cazare')
                ->formatStateUsing(fn ($state): string => $state ? 'Da' : 'Nu'),
            ExportColumn::make('confirmed')
                ->label('Confirmat')
                ->formatStateUsing(fn ($state): string => $state ? 'Da' : 'Nu'),
            ExportColumn::make('weddingTable.id')
                ->label('Masa'),
            ExportColumn::make('created_at')
                ->label('Creat la'),
            ExportColumn::make('updated_at')
                ->label('Actualizat la'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Exportul invitaților s-a finalizat și ' . Number::format($export->successful_rows) . ' ' . str('rând')->plural($export->successful_rows) . ' au fost exportate.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('rând')->plural($failedRowsCount) . ' nu au putut fi exportate.';
        }

        return $body;
    }
}
