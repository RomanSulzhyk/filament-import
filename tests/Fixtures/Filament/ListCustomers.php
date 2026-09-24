<?php

namespace RomanSulzhyk\FilamentImport\Tests\Fixtures\Filament;

use RomanSulzhyk\FilamentImport\Actions\ExcelImportAction;
use RomanSulzhyk\FilamentImport\Tests\Fixtures\CustomerImporter;
use Filament\Resources\Pages\ListRecords;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    public static array $afterImportCalls = [];

    protected function getHeaderActions(): array
    {
        return [
            // Zero-config, exactly how the pre-v5 plugin was used.
            ExcelImportAction::make()
                ->validateUsing(['name' => 'required', 'email' => 'required|email'])
                ->afterImport(function ($result) {
                    static::$afterImportCalls[] = $result;
                }),

            // An existing core Filament importer, without queues.
            ExcelImportAction::make('importWithImporter')
                ->importer(CustomerImporter::class),
        ];
    }
}
