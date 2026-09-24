<?php

namespace RomanSulzhyk\FilamentImport\Importing;

use RuntimeException;

final class TooManyRowsForSyncImport extends RuntimeException
{
    public function __construct(public readonly int $limit)
    {
        parent::__construct(__('filament-import::import.errors.too_many_rows', ['limit' => number_format($limit)]));
    }
}
