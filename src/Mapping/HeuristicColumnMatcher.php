<?php

namespace RomanSulzhyk\FilamentImport\Mapping;

use Filament\Actions\Imports\ImportColumn;

/**
 * Deterministic matching. A wrong suggestion is worse than none, because it
 * silently puts data in the wrong column, so only strong signals auto-select:
 *
 *   1. exact normalized match with the column name, label or any guess();
 *   2. compact match, so "E-mail" meets "email" and "FirstName" meets "first_name";
 *   3. match after dropping generic words: owner prefixes ("Customer Email",
 *      "Client Phone") and noise suffixes ("E-mail Address", "Phone Number");
 *   4. the whole header is a common name for the field in another language
 *      ("Місто", "Telefon", "Ciudad" for city or phone), see HeaderSynonyms;
 *   5. near-identical spelling above the threshold, for typos ("Adress").
 *
 * Containment alone never matches: "Username", "Last Name", "Company Name",
 * "Email Opt-in" and "Phone Extension" are left for the user to map.
 * Each header is used at most once, and higher-scoring pairs win conflicts.
 */
class HeuristicColumnMatcher implements ColumnMatcher
{
    protected const OWNER_PREFIXES = ['customer', 'client', 'contact', 'primary', 'main', 'your', 'the'];

    protected const NOISE_SUFFIXES = ['address', 'number', 'num', 'no', 'nr'];

    public function __construct(protected ?float $threshold = null) {}

    public function match(array $headers, array $columns, array $sampleRows = []): array
    {
        $threshold = $this->threshold ?? (float) config('filament-import.match_threshold', 0.85);
        $candidates = [];

        $compactHeaders = array_map(fn ($header) => HeaderNormalizer::compact($header), $headers);
        $targetsByColumn = [];
        $synonymsByColumn = [];
        $synonymClaims = [];

        foreach ($columns as $column) {
            $targets = array_values(array_unique(array_filter(array_map(
                fn ($guess) => HeaderNormalizer::normalize((string) $guess),
                $column->getGuesses(),
            ))));

            $synonyms = HeaderSynonyms::for(array_map(fn ($target) => str_replace(' ', '', $target), $targets), $compactHeaders);

            $targetsByColumn[$column->getName()] = $targets;
            $synonymsByColumn[$column->getName()] = $synonyms;

            foreach ($synonyms as $word) {
                $synonymClaims[$word] = ($synonymClaims[$word] ?? 0) + 1;
            }
        }

        foreach ($columns as $column) {
            // A foreign word that two columns could take is suggested for
            // neither, because guessing between them would be a coin toss.
            $synonyms = array_filter($synonymsByColumn[$column->getName()], fn ($word) => $synonymClaims[$word] === 1);

            foreach ($headers as $index => $header) {
                $score = $this->score(HeaderNormalizer::normalize($header), $targetsByColumn[$column->getName()], $threshold);

                if ($score < 0.95 && in_array($compactHeaders[$index], $synonyms, true)) {
                    $score = 0.95;
                }

                if ($score > 0) {
                    $candidates[] = [$score, $column->getName(), $header];
                }
            }
        }

        usort($candidates, fn ($a, $b) => $b[0] <=> $a[0]);

        $map = [];
        $usedHeaders = [];

        foreach ($columns as $column) {
            $map[$column->getName()] = null;
        }

        foreach ($candidates as [$score, $columnName, $header]) {
            if ($map[$columnName] !== null || isset($usedHeaders[$header])) {
                continue;
            }

            $map[$columnName] = $header;
            $usedHeaders[$header] = true;
        }

        return $map;
    }

    /**
     * @param  list<string>  $targets
     */
    protected function score(string $header, array $targets, float $threshold): float
    {
        if ($header === '') {
            return 0;
        }

        $best = 0.0;
        $compactHeader = str_replace(' ', '', $header);
        $strippedHeader = $this->stripGenericWords($header);

        foreach ($targets as $target) {
            $compactTarget = str_replace(' ', '', $target);

            if ($header === $target) {
                return 1.0;
            }

            if ($compactHeader === $compactTarget) {
                $best = max($best, 0.97);

                continue;
            }

            if ($strippedHeader !== '' && str_replace(' ', '', $strippedHeader) === $compactTarget) {
                $best = max($best, 0.92);

                continue;
            }

            if (mb_strlen($compactTarget) >= 4) {
                similar_text($compactHeader, $compactTarget, $percent);

                if ($percent / 100 >= $threshold) {
                    $best = max($best, ($percent / 100) * 0.9);
                }
            }
        }

        return $best;
    }

    protected function stripGenericWords(string $header): string
    {
        $words = explode(' ', $header);

        while (count($words) > 1 && in_array($words[0], self::OWNER_PREFIXES, true)) {
            array_shift($words);
        }

        while (count($words) > 1 && in_array(end($words), self::NOISE_SUFFIXES, true)) {
            array_pop($words);
        }

        return implode(' ', $words);
    }
}
