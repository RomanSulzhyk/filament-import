<?php

namespace RomanSulzhyk\FilamentImport\Reading;

use DateInterval;
use DateTimeInterface;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Cell\FormulaCell;
use OpenSpout\Reader\XLSX\Options;
use OpenSpout\Reader\XLSX\Reader;

/**
 * Reads the first worksheet (in workbook order, not the last active one) of an
 * .xlsx file with OpenSpout, which Filament v4 and v5 already depend on.
 * Excel dates become ISO strings and numbers become strings, so importers
 * written for CSV input (where everything is a string) keep working.
 */
class XlsxReader implements SpreadsheetReader
{
    /** Excel's own row limit. */
    public const MAX_ROWS = 1_048_576;

    /** @var list<string>|null */
    protected ?array $headers = null;

    /** File line that holds the header row. Leading blank rows are skipped. */
    protected int $headerLine = 1;

    public function __construct(protected string $path) {}

    public function headers(): array
    {
        if ($this->headers === null) {
            [$this->headers, $this->headerLine] = HeaderRow::locate($this->iterate());
        }

        return $this->headers;
    }

    public function rows(): iterable
    {
        $headers = $this->headers();
        $count = count($headers);
        $line = 0;

        foreach ($this->iterate() as $values) {
            $line++;

            if ($line <= $this->headerLine || HeaderRow::isBlank($values)) {
                continue;
            }

            $values = array_pad(array_slice($values, 0, $count), $count, null);

            yield $line => array_combine($headers, $values);
        }
    }

    /**
     * @return \Generator<int, list<mixed>>
     */
    protected function iterate(): \Generator
    {
        $options = new Options;
        $options->SHOULD_FORMAT_DATES = false;
        $options->SHOULD_PRESERVE_EMPTY_ROWS = true;

        $reader = new Reader($options);
        $reader->open($this->path);

        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                $line = 0;

                foreach ($sheet->getRowIterator() as $row) {
                    // Empty rows are generated so line numbers match the sheet.
                    // A row index past Excel's own limit can only be crafted,
                    // and would make that generation run for seconds.
                    if (++$line > static::MAX_ROWS) {
                        throw new UnsupportedSpreadsheet(__('filament-import::import.errors.unreadable'));
                    }

                    yield array_map(
                        fn (Cell $cell) => static::normalize(static::cellValue($cell)),
                        $row->getCells(),
                    );
                }

                break;
            }
        } finally {
            $reader->close();
        }
    }

    /**
     * A formula cell's getValue() is the formula text. What the user sees in
     * Excel, and what must be imported, is the value Excel cached when it last
     * calculated the sheet.
     *
     * OpenSpout also reports any plain text cell that starts with "=" as a
     * FormulaCell, with no cached value. That is text, not a formula (our own
     * failed-rows report writes such values as text on purpose), so when there
     * is no cached value the text itself is returned.
     */
    public static function cellValue(Cell $cell): mixed
    {
        if ($cell instanceof FormulaCell) {
            return $cell->getComputedValue() ?? $cell->getValue();
        }

        return $cell->getValue();
    }

    public static function normalize(mixed $value): mixed
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('H:i:s') === '00:00:00'
                ? $value->format('Y-m-d')
                : $value->format('Y-m-d H:i:s');
        }

        if ($value instanceof DateInterval) {
            // %H alone would drop whole days from durations of 24 hours or more.
            $hours = ($value->days !== false ? $value->days : $value->d) * 24 + $value->h;

            return sprintf('%02d:%02d:%02d', $hours, $value->i, $value->s);
        }

        if (is_string($value)) {
            return trim($value);
        }

        // Importers are written for CSV, where every cell is a string. Returning
        // Excel's native numbers would break string rules (a phone 5550101 fails
        // "max:40" as a number) and drop leading zeros from codes.
        if (is_int($value)) {
            return (string) $value;
        }

        if (is_float($value)) {
            return static::floatToString($value);
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return $value;
    }

    public static function floatToString(float $value): string
    {
        if (floor($value) === $value && abs($value) < 1e15) {
            return (string) (int) $value;
        }

        // Shortest representation that round-trips, without scientific notation.
        $string = json_encode($value);

        if (stripos($string, 'e') !== false) {
            $string = rtrim(rtrim(number_format($value, 12, '.', ''), '0'), '.');
        }

        return $string;
    }
}
