<?php

namespace App\Filament\Resources\Invitats\Pages;

use App\Filament\Imports\InvitatImporter;
use App\Filament\Resources\Invitats\InvitatResource;
use App\Models\Invitat;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Resources\Pages\ListRecords;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListInvitats extends ListRecords
{
    protected static string $resource = InvitatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('mese_config')
                ->label('Configurare mese')
                ->icon('heroicon-o-cog-6-tooth')
                ->url('/mese/config'),
            ImportAction::make()
                ->importer(InvitatImporter::class),
            Action::make('export')
                ->label('Export')
                ->icon('heroicon-o-arrow-down-tray')
                ->schema([
                    CheckboxList::make('columns')
                        ->label('Coloane')
                        ->options(self::exportColumns())
                        ->default(array_keys(self::exportColumns()))
                        ->required()
                        ->columns(2),
                ])
                ->action(fn (array $data): StreamedResponse => $this->streamXlsx($data['columns'])),
            CreateAction::make(),
        ];
    }

    private static function exportColumns(): array
    {
        return [
            'name' => 'Nume',
            'name_initial' => 'Inițială',
            'person_number' => 'Adulți',
            'kid_number' => 'Copii',
            'total_guests' => 'Total invitați',
            'accommodation' => 'Cazare',
            'confirmed' => 'Confirmat',
            'wedding_table_id' => 'Masa',
            'created_at' => 'Creat la',
            'updated_at' => 'Actualizat la',
        ];
    }

    private function streamXlsx(array $selectedColumns): StreamedResponse
    {
        $labels = self::exportColumns();
        $columns = array_values(array_intersect(array_keys($labels), $selectedColumns));

        $query = $this->getFilteredTableQuery();
        $filename = 'invitati-' . now()->format('Y-m-d-His') . '.xlsx';

        return response()->streamDownload(function () use ($columns, $labels, $query): void {
            $writer = new Writer();
            $writer->openToFile('php://output');

            $writer->addRow(Row::fromValues(array_map(fn (string $c): string => $labels[$c], $columns)));

            $query->lazy(500)->each(function (Invitat $invitat) use ($writer, $columns): void {
                $values = array_map(function (string $c) use ($invitat) {
                    return match ($c) {
                        'name_initial' => mb_strtoupper(mb_substr((string) $invitat->name, 0, 1)),
                        'total_guests' => (int) $invitat->person_number + (int) $invitat->kid_number,
                        'accommodation', 'confirmed' => $invitat->{$c} ? 'Da' : 'Nu',
                        'created_at', 'updated_at' => $invitat->{$c}?->format('Y-m-d H:i:s'),
                        'wedding_table_id' => $invitat->weddingTable?->number,
                        default => $invitat->{$c},
                    };
                }, $columns);

                $writer->addRow(Row::fromValues($values));
            });

            $writer->close();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
