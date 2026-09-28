<?php

namespace RomanSulzhyk\FilamentImport\Tests\Fixtures;

use Filament\Actions\Imports\ImportColumn;
use Filament\Forms\Components\TextInput;

class CustomerOptionsImporter extends CustomerImporter
{
    public static array $seenOptions = [];

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')->requiredMapping()->rules(['required']),
            ImportColumn::make('email')->requiredMapping()->rules(['required', 'email']),
        ];
    }

    public static function getOptionsFormComponents(): array
    {
        return [TextInput::make('city')->required()];
    }

    protected function beforeSave(): void
    {
        static::$seenOptions = $this->options;
        $this->record->city = $this->options['city'];
    }
}
