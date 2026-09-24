<?php

use RomanSulzhyk\FilamentImport\Actions\ExcelImportAction;
use RomanSulzhyk\FilamentImport\Tests\Fixtures\Filament\ListTeamCustomers;
use RomanSulzhyk\FilamentImport\Tests\Fixtures\Team;
use RomanSulzhyk\FilamentImport\Tests\Fixtures\User;
use Filament\Facades\Filament;

it('M5: never offers the tenant ownership key on a tenant-scoped resource', function () {
    $team = Team::create(['name' => 'Acme']);
    $this->actingAs(User::create(['name' => 'Roman', 'email' => 'roman@example.com', 'password' => 'x']));

    Filament::setCurrentPanel(Filament::getPanel('tenant'));
    Filament::setTenant($team);

    $action = ExcelImportAction::make()->upsertBy('email');
    $action->livewire(new ListTeamCustomers);

    $names = array_map(fn ($column) => $column->getName(), $action->getImportColumns());

    expect($names)->toBe(['name', 'email'])
        ->not->toContain('team_id');
});

it('M5: offers every fillable column where there is no tenancy', function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    $action = ExcelImportAction::make()->importModel(\RomanSulzhyk\FilamentImport\Tests\Fixtures\TeamCustomer::class);
    $action->livewire(new ListTeamCustomers);

    $names = array_map(fn ($column) => $column->getName(), $action->getImportColumns());

    expect($names)->toBe(['team_id', 'name', 'email']);
});
