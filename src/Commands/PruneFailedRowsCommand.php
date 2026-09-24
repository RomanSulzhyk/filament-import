<?php

namespace RomanSulzhyk\FilamentImport\Commands;

use RomanSulzhyk\FilamentImport\Importing\FailedRowsWriter;
use Illuminate\Console\Command;

class PruneFailedRowsCommand extends Command
{
    protected $signature = 'filament-import:prune';

    protected $description = 'Delete failed-row import reports older than filament-import.failed_rows_ttl_minutes.';

    public function handle(): int
    {
        $this->info('Deleted ' . FailedRowsWriter::prune() . ' expired failed-row reports.');

        return self::SUCCESS;
    }
}
