<?php

use RomanSulzhyk\FilamentImport\Http\DownloadFailedRowsController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')
    ->get('filament-import/failed-rows/{file}', DownloadFailedRowsController::class)
    ->name('filament-import.failed-rows');
