<?php

namespace RomanSulzhyk\FilamentImport\Reading;

class HeaderRow
{
    /** Data rows read after the header to find unheaded columns that hold data. */
    public const SAMPLE_ROWS = 200;

    /** Unheaded columns beyond this position are ignored. */
    public const MAX_COLUMNS = 256;

    /**
     * Turns the header row into unique, non-empty column names.
     *
     * A column with no header gets the name `column_N`. Trailing unnamed
     * columns are dropped when they are empty, because Excel often reports
     * formatted-but-empty columns. One that holds data in the sampled rows is
     * kept, so its values still reach the failed-rows report.
     *
     * @param  array<mixed>  $values
     * @param  iterable<array<mixed>>  $sampleRows  Rows that follow the header.
     * @return list<string>
     */
    public static function clean(array $values, iterable $sampleRows = []): array
    {
        $values = array_values($values);
        $filled = [];

        foreach ($sampleRows as $row) {
            foreach (array_values($row) as $index => $value) {
                if ($value !== null && trim((string) $value) !== '') {
                    $filled[$index] = true;
                }
            }
        }

        // Unheaded columns are only added up to a sane width, so one stray cell
        // in column XFD cannot create sixteen thousand mapping options.
        $filled = array_filter($filled, fn ($_, $index) => $index < static::MAX_COLUMNS, ARRAY_FILTER_USE_BOTH);
        $width = max(count($values), $filled === [] ? 0 : max(array_keys($filled)) + 1);
        $values = array_pad($values, $width, null);

        $headers = [];
        $seen = [];

        foreach ($values as $index => $value) {
            $header = trim((string) $value);

            if ($header === '') {
                $header = 'column_' . ($index + 1);
            }

            // Duplicate headers would silently overwrite each other in array_combine.
            $base = $header;
            $suffix = 2;

            while (isset($seen[$header])) {
                $header = "{$base}_{$suffix}";
                $suffix++;
            }

            $seen[$header] = true;
            $headers[] = $header;
        }

        while ($headers !== [] && preg_match('/^column_\d+$/', end($headers)) && ! isset($filled[count($headers) - 1])) {
            array_pop($headers);
        }

        return $headers;
    }

    /**
     * @param  array<mixed>  $values
     */
    public static function isBlank(array $values): bool
    {
        foreach ($values as $value) {
            if ($value !== null && trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * Finds the header row (the first non-blank row) and the sample of data
     * rows after it.
     *
     * @param  iterable<array<mixed>>  $rows
     * @return array{0: list<string>, 1: int} Headers and the 1-based line of the header row.
     */
    public static function locate(iterable $rows): array
    {
        $line = 0;
        $headerValues = null;
        $headerLine = 0;
        $sample = [];

        foreach ($rows as $values) {
            $line++;
            $values = array_values($values);

            if ($headerValues === null) {
                if (! static::isBlank($values)) {
                    $headerValues = $values;
                    $headerLine = $line;
                }

                continue;
            }

            if (! static::isBlank($values)) {
                $sample[] = $values;
            }

            if (count($sample) >= static::SAMPLE_ROWS) {
                break;
            }
        }

        return $headerValues === null ? [[], 0] : [static::clean($headerValues, $sample), $headerLine];
    }
}
