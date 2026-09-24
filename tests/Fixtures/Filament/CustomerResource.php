<?php

namespace RomanSulzhyk\FilamentImport\Tests\Fixtures\Filament;

use RomanSulzhyk\FilamentImport\Tests\Fixtures\Customer;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name'),
            TextColumn::make('email'),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListCustomers::route('/')];
    }
}
