<?php

namespace RomanSulzhyk\FilamentImport\Reading;

interface SpreadsheetReader
{
    /**
     * The header row, in file order. Blank headers are replaced with
     * "column_{n}" so every cell can still be addressed.
     *
     * @return list<string>
     */
    public function headers(): array;

    /**
     * Data rows keyed by header. The generator key is the 1-based line number
     * in the source file, so a failure can point at the exact spreadsheet row
     * without reserving a column name. Rows whose cells are all blank are
     * skipped but still counted in the numbering.
     *
     * @return iterable<int, array<string, mixed>>
     */
    public function rows(): iterable;
}
