<?php

namespace RomanSulzhyk\FilamentImport\Tests\Fixtures\Filament;

use RomanSulzhyk\FilamentImport\Actions\ExcelImportAction;
use RomanSulzhyk\FilamentImport\Tests\Fixtures\CustomerImporter;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;
use RomanSulzhyk\FilamentImport\Tests\Fixtures\CustomerOptionsImporter;

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

            // Fixed values: an array hides the column from mapping.
            ExcelImportAction::make('importWithFixedCity')
                ->additionalData(['city' => 'Kyiv']),

            // A modal field feeding a fixed value through a closure.
            ExcelImportAction::make('importWithDefaults')
                ->optionsFormComponents([TextInput::make('default_city')->required()])
                ->additionalData(fn (array $data) => ['city' => $data['default_city']]),

            // A system that labels CSV files with a type the defaults lack.
            ExcelImportAction::make('importWithExtraTypes')
                ->acceptedFileTypes([...ExcelImportAction::ACCEPTED_FILE_TYPES, 'application/octet-stream']),

            // An importer with its own options form, as in the Filament docs.
            ExcelImportAction::make('importWithOptions')
                ->importer(CustomerOptionsImporter::class)
                ->importerOptions(['source' => 'code']),
        ];
    }
}
