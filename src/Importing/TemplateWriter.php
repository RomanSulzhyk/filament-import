<?php

namespace RomanSulzhyk\FilamentImport\Importing;

use Filament\Actions\Imports\ImportColumn;
use OpenSpout\Common\Entity\Cell\StringCell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

class TemplateWriter
{
    /**
     * An .xlsx with one header per import column and, below it, the examples
     * the columns declare (Filament's ->example(), ->examples() and, from
     * Filament 5.9, the cases of ->enum()). Cells are text, like the failure
     * report, so an example such as "0044" or "=1" is kept as written.
     *
     * @param  array<ImportColumn>  $columns
     * @return string Path of a local temporary file.
     */
    public static function write(array $columns): string
    {
        $examples = array_map(fn (ImportColumn $column): array => array_values($column->getExamples()), $columns);
        $rows = array_reduce($examples, fn (int $count, array $values): int => max($count, count($values)), 0);

        $path = tempnam(sys_get_temp_dir(), 'filament-import-template-');

        try {
            $writer = new Writer;
            $writer->openToFile($path);

            try {
                $writer->addRow(static::textRow(array_map(fn (ImportColumn $column): string => $column->getExampleHeader(), $columns)));

                for ($i = 0; $i < $rows; $i++) {
                    $writer->addRow(static::textRow(array_map(fn (array $values): string => (string) ($values[$i] ?? ''), $examples)));
                }
            } finally {
                $writer->close();
            }
        } catch (\Throwable $e) {
            @unlink($path);

            throw $e;
        }

        return $path;
    }

    /**
     * @param  list<string>  $values
     */
    protected static function textRow(array $values): Row
    {
        return new Row(array_map(fn (string $value) => new StringCell($value, null), $values));
    }
}
