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

it('refuses fixed values that would set the tenant ownership key', function () {
    $team = Team::create(['name' => 'Acme']);
    $this->actingAs(User::create(['name' => 'Roman', 'email' => 'roman@example.com', 'password' => 'x']));

    Filament::setCurrentPanel(Filament::getPanel('tenant'));
    Filament::setTenant($team);

    $action = ExcelImportAction::make()->additionalData(['team_id' => 999]);
    $action->livewire(new ListTeamCustomers);

    expect(fn () => $action->getImportColumns())->toThrow(LogicException::class, 'tenant ownership key');
});

it('refuses a tenant key returned by an additionalData() closure, without touching records', function () {
    $a = Team::create(['name' => 'A']);
    $b = Team::create(['name' => 'B']);
    $this->actingAs(User::create(['name' => 'Roman', 'email' => 'roman@example.com', 'password' => 'x']));

    Filament::setCurrentPanel(Filament::getPanel('tenant'));
    Filament::setTenant($a);
    \RomanSulzhyk\FilamentImport\Tests\Fixtures\TeamCustomer::create(['team_id' => $a->id, 'name' => 'Old', 'email' => 'x@example.com']);

    $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('c.csv', "name,email\nNew,x@example.com\n");

    expect(fn () => \Pest\Livewire\livewire(ClosureTenantPage::class)
        ->mountAction('excelImport')
        ->fillForm(['file' => $file, 'team' => (string) $b->id])
        ->callMountedAction())->toThrow(LogicException::class, 'tenant ownership key');

    expect(\RomanSulzhyk\FilamentImport\Tests\Fixtures\TeamCustomer::withoutGlobalScopes()->pluck('team_id')->all())->toBe([$a->id]);
});

class ClosureTenantPage extends ListTeamCustomers
{
    protected function getHeaderActions(): array
    {
        return [ExcelImportAction::make()->upsertBy('email')
            ->optionsFormComponents([\Filament\Forms\Components\TextInput::make('team')])
            ->additionalData(fn (array $data) => ['team_id' => (int) $data['team']])];
    }
}
