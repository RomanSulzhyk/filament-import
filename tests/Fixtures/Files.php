<?php

namespace RomanSulzhyk\FilamentImport\Tests\Fixtures;

use DateTimeInterface;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;

class Files
{
    /**
     * @param  list<list<mixed>>  $rows  First row is the header.
     */
    public static function csv(array $rows, string $delimiter = ',', string $encoding = 'UTF-8', bool $bom = false): string
    {
        $path = tempnam(sys_get_temp_dir(), 'fi-') . '.csv';
        $handle = fopen($path, 'w');

        if ($bom) {
            fwrite($handle, "\xEF\xBB\xBF");
        }

        foreach ($rows as $row) {
            $line = implode($delimiter, array_map(
                fn ($value) => '"' . str_replace('"', '""', (string) $value) . '"',
                $row,
            )) . "\n";

            fwrite($handle, $encoding === 'UTF-8' ? $line : mb_convert_encoding($line, $encoding, 'UTF-8'));
        }

        fclose($handle);

        return $path;
    }

    /**
     * @param  list<list<mixed>>  $rows  First row is the header. DateTime values become real Excel dates.
     */
    public static function xlsx(array $rows): string
    {
        $path = tempnam(sys_get_temp_dir(), 'fi-') . '.xlsx';
        $writer = new Writer;
        $writer->openToFile($path);

        // Real spreadsheets carry a date number format on date cells; without it
        // Excel itself shows (and OpenSpout reads) the raw serial number.
        $dateStyle = (new Style)->setFormat('yyyy-mm-dd');

        foreach ($rows as $row) {
            $writer->addRow(new Row(array_map(
                fn ($value) => $value instanceof DateTimeInterface
                    ? Cell::fromValue($value, $dateStyle)
                    : Cell::fromValue($value),
                $row,
            )));
        }

        $writer->close();

        return $path;
    }

    /**
     * An .xlsx whose given cells hold a formula plus the value Excel cached for
     * it, stored exactly the way Excel stores it: <c><f>formula</f><v>value</v></c>.
     * OpenSpout's writer cannot produce this, which is why a formula bug once
     * passed every other test.
     *
     * @param  list<list<mixed>>  $rows
     * @param  array<string, array{formula: string, value: string, type?: string}>  $formulas  Cell ref (e.g. "B2") => formula.
     */
    public static function xlsxWithFormulas(array $rows, array $formulas): string
    {
        $path = static::xlsx($rows);
        $zip = new \ZipArchive;
        $zip->open($path);
        $xml = $zip->getFromName('xl/worksheets/sheet1.xml');

        foreach ($formulas as $ref => $formula) {
            $type = isset($formula['type']) ? ' t="' . $formula['type'] . '"' : '';
            $cell = '<c r="' . $ref . '"' . $type . '><f>' . htmlspecialchars($formula['formula'], ENT_XML1) . '</f><v>' . htmlspecialchars($formula['value'], ENT_XML1) . '</v></c>';
            $xml = preg_replace('#<c r="' . $ref . '"[^>]*>.*?</c>#s', $cell, $xml, 1);
        }

        $zip->addFromString('xl/worksheets/sheet1.xml', $xml);
        $zip->close();

        return $path;
    }
}
