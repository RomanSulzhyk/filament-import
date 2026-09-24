<?php

namespace RomanSulzhyk\FilamentImport\Tests\Fixtures\Filament;

use RomanSulzhyk\FilamentImport\Actions\ExcelImportAction;
use Filament\Resources\Pages\ListRecords;

class ListTeamCustomers extends ListRecords
{
    protected static string $resource = TeamCustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [ExcelImportAction::make()->upsertBy('email')];
    }
}
