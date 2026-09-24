<?php

namespace RomanSulzhyk\FilamentImport\Tests\Fixtures\Filament;

use RomanSulzhyk\FilamentImport\Tests\Fixtures\Team;
use Filament\Panel;
use Filament\PanelProvider;

class TenantPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('tenant')
            ->path('tenant')
            ->tenant(Team::class)
            ->resources([TeamCustomerResource::class]);
    }
}
