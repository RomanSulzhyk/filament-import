<?php

namespace RomanSulzhyk\FilamentImport;

use RomanSulzhyk\FilamentImport\Commands\PruneFailedRowsCommand;
use RomanSulzhyk\FilamentImport\Mapping\ColumnMatcher;
use RomanSulzhyk\FilamentImport\Mapping\HeuristicColumnMatcher;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentImportServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-import';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile()
            ->hasTranslations()
            ->hasRoute('web')
            ->hasCommand(PruneFailedRowsCommand::class);
    }

    public function packageRegistered(): void
    {
        // The pro package rebinds this to its AI-assisted matcher.
        $this->app->bindIf(ColumnMatcher::class, HeuristicColumnMatcher::class);
    }
}
