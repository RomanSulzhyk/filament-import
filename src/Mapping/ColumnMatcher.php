<?php

namespace RomanSulzhyk\FilamentImport\Mapping;

use Filament\Actions\Imports\ImportColumn;

interface ColumnMatcher
{
    /**
     * Propose which file header feeds each import column.
     *
     * @param  list<string>  $headers  File headers in file order.
     * @param  array<ImportColumn>  $columns
     * @param  list<array<string, mixed>>  $sampleRows  A few data rows, for matchers that look at values.
     * @return array<string, string|null>  Column name => file header, or null when nothing fits.
     */
    public function match(array $headers, array $columns, array $sampleRows = []): array;
}
