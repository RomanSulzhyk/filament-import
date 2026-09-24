<?php

namespace RomanSulzhyk\FilamentImport\Reading;

use League\Csv\CharsetConverter;
use League\Csv\Info;
use League\Csv\Reader;

/**
 * Reads CSV the way Filament's own importer does (delimiter sniffing across
 * , ; | and tab, UTF-8 output), and additionally tolerates a BOM, Windows-1251
 * and Latin-1 files, which is what spreadsheets exported from Excel often are.
 */
class CsvReader implements SpreadsheetReader
{
    protected const SAMPLE_BYTES = 65536;

    protected Reader $csv;

    /** @var list<string>|null */
    protected ?array $headers = null;

    /** File line that holds the header row. Leading blank lines are skipped. */
    protected int $headerLine = 1;

    public function __construct(protected string $path, protected ?string $delimiter = null)
    {
        $this->csv = Reader::createFromPath($path, 'r');
        $this->csv->skipInputBOM();

        // Keep physical blank lines so reported line numbers match the file.
        // Blank records are skipped explicitly in rows().
        $this->csv->includeEmptyRecords();

        $encoding = static::detectEncoding($path);

        if ($encoding !== null && strtoupper($encoding) !== 'UTF-8') {
            CharsetConverter::addTo($this->csv, $encoding, 'UTF-8');
        }

        $this->csv->setDelimiter($delimiter ?? $this->sniffDelimiter());
    }

    public function headers(): array
    {
        if ($this->headers === null) {
            [$this->headers, $this->headerLine] = HeaderRow::locate($this->csv->getRecords());
        }

        return $this->headers;
    }

    public function rows(): iterable
    {
        $headers = $this->headers();
        $count = count($headers);
        $line = 0;

        foreach ($this->csv->getRecords() as $record) {
            $line++;

            if ($line <= $this->headerLine) {
                continue;
            }

            $values = array_values($record);

            if (HeaderRow::isBlank($values)) {
                continue;
            }

            $values = array_pad(array_slice($values, 0, $count), $count, null);

            yield $line => array_combine($headers, array_map(
                fn ($value) => is_string($value) ? trim($value) : $value,
                $values,
            ));
        }
    }

    protected function sniffDelimiter(): string
    {
        $stats = Info::getDelimiterStats($this->csv, [',', ';', '|', "\t"], 10);
        arsort($stats);

        return (string) array_key_first($stats) ?: ',';
    }

    public static function detectEncoding(string $path): ?string
    {
        $sample = (string) file_get_contents($path, length: static::SAMPLE_BYTES);

        // The sample is a fixed number of bytes, so it can end in the middle of
        // a multibyte character. Judging that truncated tail would make a valid
        // UTF-8 file look invalid and send it down the legacy-encoding path.
        $sample = static::trimToCompleteLines($sample, reachedEof: strlen($sample) < static::SAMPLE_BYTES);

        if ($sample === '' || mb_check_encoding($sample, 'UTF-8')) {
            return 'UTF-8';
        }

        // Every byte decodes in both Windows-1251 and Windows-1252, so the
        // choice rests on what the text looks like. Decoded as 1251, a real
        // Cyrillic file has whole Cyrillic words, while Western European text
        // turns into words that mix Latin and Cyrillic letters ("Hфtel",
        // "Mьller"). Mixed words almost never occur in genuine Cyrillic text.
        $cyrillic = @mb_convert_encoding($sample, 'UTF-8', 'Windows-1251');
        preg_match_all('/[\p{L}]+/u', $cyrillic, $words);
        $pure = 0;
        $mixed = 0;

        foreach ($words[0] as $word) {
            // One-letter "words" prove nothing: in Windows-1252, "à", "é", "€"
            // and "£" decode as the single Cyrillic letters "а", "й", "Ђ", "Ј".
            // Only the core Cyrillic alphabet (А-я) counts as evidence.
            if (mb_strlen($word) < 2) {
                continue;
            }

            $hasCyrillic = preg_match('/[\x{0410}-\x{044F}]/u', $word) === 1;
            // A Latin "i" is not evidence: Ukrainian typed on a Russian layout
            // often uses it in place of "і" ("Мiсто", "Львiв").
            $hasLatin = preg_match('/[A-HJ-Za-hj-z]/', $word) === 1;

            if ($hasCyrillic && $hasLatin) {
                $mixed++;
            } elseif ($hasCyrillic) {
                $pure++;
            }
        }

        if ($pure > 0 && $pure > $mixed) {
            return 'Windows-1251';
        }

        if (mb_check_encoding(@mb_convert_encoding($sample, 'UTF-8', 'Windows-1252'), 'UTF-8')) {
            return 'Windows-1252';
        }

        return null;
    }

    /**
     * Cut a byte sample back to its last line break, so the encoding check
     * only sees whole lines. If the sample has no line break at all, drop up
     * to three trailing bytes that could be an incomplete UTF-8 sequence.
     */
    public static function trimToCompleteLines(string $sample, bool $reachedEof): string
    {
        if ($reachedEof) {
            return $sample;
        }

        $lastNewline = strrpos($sample, "\n");

        if ($lastNewline !== false) {
            return substr($sample, 0, $lastNewline + 1);
        }

        for ($drop = 0; $drop <= 3; $drop++) {
            $candidate = substr($sample, 0, strlen($sample) - $drop);

            if (mb_check_encoding($candidate, 'UTF-8')) {
                return $candidate;
            }
        }

        return $sample;
    }
}
