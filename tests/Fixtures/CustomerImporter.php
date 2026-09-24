<?php

namespace RomanSulzhyk\FilamentImport\Tests\Fixtures;

use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

/**
 * A normal Filament core importer, written exactly as the Filament docs show.
 * The package must be able to run it without any change.
 */
class CustomerImporter extends Importer
{
    protected static ?string $model = Customer::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')->requiredMapping()->rules(['required', 'max:255']),
            ImportColumn::make('email')->requiredMapping()->rules(['required', 'email'])->guess(['e-mail', 'mail']),
            ImportColumn::make('phone')->rules(['nullable', 'max:40']),
            ImportColumn::make('city')->rules(['nullable', 'max:255']),
            ImportColumn::make('joined_at')->rules(['nullable', 'date'])->guess(['joined', 'signup date']),
        ];
    }

    public function resolveRecord(): ?Customer
    {
        return Customer::firstOrNew(['email' => $this->data['email']]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        return 'Done.';
    }
}
