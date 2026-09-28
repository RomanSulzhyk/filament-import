<?php

namespace RomanSulzhyk\FilamentImport\Mapping;

/**
 * Common field names in every language Filament is translated into, one
 * file per language in resources/synonyms.
 *
 * A header matches a synonym only when the whole header equals it after
 * normalization, never by containment or similar spelling, so "Місто" fills a
 * `city` column but "Місто доставки" is left for the user to map.
 *
 * Each word belongs to exactly one field, and each field to a disjoint set of
 * English keys, so related fields (phone and mobile, address and street, notes
 * and comment) never trade values. A word that could still reach two columns
 * is suggested for neither; see HeuristicColumnMatcher. Words that usually
 * mean something else in real exports ("Estado" is often a state, "Пошта" is
 * often the delivery carrier) are deliberately absent.
 */
class HeaderSynonyms
{
    /**
     * The English keys that identify each field. Every key belongs to exactly
     * one field, so related fields never trade values.
     *
     * @var array<string, list<string>>
     */
    public const KEYS = [
        'name' => ['name', 'fullname'],
        'first_name' => ['firstname', 'givenname'],
        'last_name' => ['lastname', 'surname', 'familyname'],
        'email' => ['email', 'emailaddress', 'mail'],
        'phone' => ['phone', 'phonenumber', 'telephone', 'tel'],
        'mobile' => ['mobile', 'mobilephone', 'mobilenumber', 'cell', 'cellphone'],
        'city' => ['city', 'town'],
        'country' => ['country'],
        'address' => ['address', 'streetaddress', 'address1', 'addressline1'],
        'street' => ['street'],
        'postal_code' => ['zip', 'zipcode', 'postcode', 'postalcode'],
        'company' => ['company', 'companyname', 'organization', 'organisation'],
        'price' => ['price'],
        'quantity' => ['quantity', 'qty'],
        'description' => ['description'],
        'title' => ['title'],
        'sku' => ['sku'],
        'category' => ['category'],
        'notes' => ['notes', 'note'],
        'comment' => ['comment', 'comments'],
        'status' => ['status'],
        'date_of_birth' => ['dateofbirth', 'birthdate', 'birthday', 'dob'],
    ];

    /** @var array<string, array{keys: list<string>, words: list<string>, given: list<string>}>|null */
    protected static ?array $groups = null;

    /**
     * Every field with its keys and its words from all languages in
     * resources/synonyms. Each file holds one language, keyed by field;
     * "given" lists first names that may fill a full-name field.
     *
     * @return array<string, array{keys: list<string>, words: list<string>, given: list<string>}>
     */
    public static function groups(): array
    {
        if (static::$groups !== null) {
            return static::$groups;
        }

        $groups = [];

        foreach (static::KEYS as $field => $keys) {
            $groups[$field] = ['keys' => $keys, 'words' => [], 'given' => []];
        }

        foreach (static::languageFiles() as $file) {
            foreach (require $file as $field => $words) {
                if ($field === 'given') {
                    $groups['name']['given'] = [...$groups['name']['given'], ...$words];
                } elseif (isset($groups[$field])) {
                    $groups[$field]['words'] = [...$groups[$field]['words'], ...$words];
                }
            }
        }

        return static::$groups = $groups;
    }

    /**
     * @return list<string>
     */
    public static function languageFiles(): array
    {
        $files = glob(dirname(__DIR__, 2).'/resources/synonyms/*.php') ?: [];
        sort($files);

        return $files;
    }

    /**
     * Compact normalized synonyms for a column, given the compact forms of its
     * name and guesses, and of every header in the file.
     *
     * @param  list<string>  $compactTargets
     * @param  list<string>  $compactHeaders
     * @return list<string>
     */
    public static function for(array $compactTargets, array $compactHeaders = []): array
    {
        $words = [];

        foreach (static::groups() as $field => $group) {
            if (array_intersect($compactTargets, $group['keys']) === []) {
                continue;
            }

            $groupWords = $group['words'];

            // "Ім'я", "Nombre" or "이름" heads a full-name column when the file
            // has no surname column, and a first-name column when it has one,
            // so a full name never lands in a first-name field. Without a
            // surname column the matcher also counts first_name as a claimant
            // (see blockedWords()), so a model with both fields gets neither.
            if ($field === 'name' && ! static::hasSurnameHeader($compactHeaders)) {
                $groupWords = [...$groupWords, ...$group['given']];
            } elseif ($field === 'first_name' && static::hasSurnameHeader($compactHeaders)) {
                $groupWords = [...$groupWords, ...static::groups()['name']['given']];
            }

            foreach ($groupWords as $word) {
                $words[] = HeaderNormalizer::compact($word);
            }
        }

        return array_values(array_unique(array_filter($words)));
    }

    /**
     * Words a column cannot take but still competes for: without a surname
     * column, a first name is as good a guess for first_name as for name.
     *
     * @param  list<string>  $compactTargets
     * @param  list<string>  $compactHeaders
     * @return list<string>
     */
    public static function blockedWords(array $compactTargets, array $compactHeaders = []): array
    {
        if (array_intersect($compactTargets, static::KEYS['first_name']) === [] || static::hasSurnameHeader($compactHeaders)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map(
            fn (string $word) => HeaderNormalizer::compact($word),
            static::groups()['name']['given'],
        ))));
    }

    /**
     * @param  list<string>  $compactHeaders
     */
    public static function hasSurnameHeader(array $compactHeaders): bool
    {
        $lastName = static::groups()['last_name'];
        $surname = [...$lastName['keys'], ...array_map(
            fn (string $word) => HeaderNormalizer::compact($word),
            $lastName['words'],
        )];

        return array_intersect($compactHeaders, $surname) !== [];
    }
}
