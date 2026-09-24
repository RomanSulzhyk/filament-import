<?php

namespace RomanSulzhyk\FilamentImport\Tests\Fixtures;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;

/** A second, separately authenticated user type, like a panel on an "admin" guard. */
class Admin extends Authenticatable implements FilamentUser
{
    protected $table = 'admins';

    protected $guarded = [];

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
