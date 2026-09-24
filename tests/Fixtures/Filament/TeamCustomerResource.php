<?php

namespace RomanSulzhyk\FilamentImport\Tests\Fixtures\Filament;

use RomanSulzhyk\FilamentImport\Tests\Fixtures\TeamCustomer;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TeamCustomerResource extends Resource
{
    protected static ?string $model = TeamCustomer::class;

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('name')]);
    }

    public static function getPages(): array
    {
        return ['index' => ListTeamCustomers::route('/')];
    }
}
