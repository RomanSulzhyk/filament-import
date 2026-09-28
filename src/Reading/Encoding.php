<?php

namespace RomanSulzhyk\FilamentImport\Reading;

use InvalidArgumentException;

/**
 * Detects and decodes the encodings CSV files really arrive in.
 *
 * Excel's "CSV" format is saved in the Windows code page of the machine that
 * saved it, which follows the user's language, and its "Unicode Text" format
 * and some ad platforms' exports are UTF-16. Bytes alone cannot tell most code
 * pages apart, so the app's locale is used as a hint: a Polish app is offered
 * Windows-1250 first, a Japanese app Shift_JIS. The hint is only taken when
 * the decoded text looks like real text in that script.
 */
final class Encoding
{
    /**
     * The Windows code page Excel uses for CSV in each language. Cyrillic
     * languages are absent because Windows-1251 is recognized without a hint.
     *
     * @var array<string, string>
     */
    public const LOCALE_CODE_PAGES = [
        'bs' => 'Windows-1250', 'cs' => 'Windows-1250', 'hr' => 'Windows-1250', 'hu' => 'Windows-1250',
        'pl' => 'Windows-1250', 'ro' => 'Windows-1250', 'sk' => 'Windows-1250', 'sl' => 'Windows-1250',
        'sq' => 'Windows-1250', 'sr_latn' => 'Windows-1250',
        'el' => 'Windows-1253',
        'tr' => 'Windows-1254', 'az' => 'Windows-1254',
        'he' => 'Windows-1255',
        'ar' => 'Windows-1256', 'fa' => 'Windows-1256', 'ur' => 'Windows-1256', 'ckb' => 'Windows-1256',
        'et' => 'Windows-1257', 'lt' => 'Windows-1257', 'lv' => 'Windows-1257',
        'th' => 'Windows-874',
        'ja' => 'CP932',
        'ko' => 'CP949',
        'zh' => 'CP936', 'zh_cn' => 'CP936',
        'zh_tw' => 'CP950', 'zh_hk' => 'CP950',
    ];

    /** Script whose letters prove a decoding right, per code page. */
    protected const SCRIPTS = [
        'Windows-1250' => 'Latin', 'Windows-1254' => 'Latin', 'Windows-1257' => 'Latin', 'Windows-1258' => 'Latin',
        'Windows-1253' => 'Greek', 'Windows-1255' => 'Hebrew', 'Windows-1256' => 'Arabic', 'Windows-874' => 'Thai',
        'CP932' => 'CJK', 'CP936' => 'CJK', 'CP950' => 'CJK', 'CP949' => 'CJK', 'GB18030' => 'CJK',
    ];

    protected const ALIASES = [
        'CP1250' => 'Windows-1250', 'CP1251' => 'Windows-1251', 'CP1252' => 'Windows-1252',
        'CP1253' => 'Windows-1253', 'CP1254' => 'Windows-1254', 'CP1255' => 'Windows-1255',
        'CP1256' => 'Windows-1256', 'CP1257' => 'Windows-1257', 'CP1258' => 'Windows-1258',
        'CP874' => 'Windows-874', 'TIS-620' => 'Windows-874',
        'SHIFT_JIS' => 'CP932', 'SHIFT-JIS' => 'CP932', 'SJIS' => 'CP932', 'SJIS-WIN' => 'CP932',
        'GBK' => 'CP936', 'GB2312' => 'CP936',
        'BIG5' => 'CP950', 'BIG-5' => 'CP950',
        'EUC-KR' => 'CP949', 'UHC' => 'CP949',
        'LATIN1' => 'Windows-1252', 'ISO-8859-1' => 'Windows-1252',
        'UTF8' => 'UTF-8',
    ];

    public static function forLocale(?string $locale): ?string
    {
        if ($locale === null || $locale === '') {
            return null;
        }

        $locale = strtolower(str_replace('-', '_', $locale));

        return self::LOCALE_CODE_PAGES[$locale] ?? self::LOCALE_CODE_PAGES[explode('_', $locale)[0]] ?? null;
    }

    /**
     * The canonical name of an encoding given by a developer, such as
     * "cp1250", "Shift_JIS" or "Big5".
     */
    public static function canonical(string $encoding): string
    {
        $upper = strtoupper(trim($encoding));

        if (isset(self::ALIASES[$upper])) {
            return self::ALIASES[$upper];
        }

        foreach ([...array_keys(CodePages::TABLES), 'Windows-1251', 'Windows-1252', 'UTF-8', 'UTF-16', 'UTF-16LE', 'UTF-16BE', 'CP932', 'CP936', 'CP949', 'CP950', 'GB18030'] as $known) {
            if ($upper === strtoupper($known)) {
                return $known;
            }
        }

        if (in_array($upper, array_map('strtoupper', mb_list_encodings()), true)) {
            return $encoding;
        }

        throw new InvalidArgumentException("Unsupported CSV encoding [{$encoding}].");
    }

    /**
     * Decode bytes to UTF-8, or null when they are not valid in the encoding.
     */
    public static function decode(string $bytes, string $encoding): ?string
    {
        $encoding = self::canonical($encoding);

        if ($encoding === 'UTF-8') {
            return mb_check_encoding($bytes, 'UTF-8') ? $bytes : null;
        }

        if (str_starts_with($encoding, 'UTF-16')) {
            if ($encoding === 'UTF-16') {
                $encoding = str_starts_with($bytes, "\xFE\xFF") ? 'UTF-16BE' : 'UTF-16LE';
            }

            if (str_starts_with($bytes, "\xFF\xFE") || str_starts_with($bytes, "\xFE\xFF")) {
                $bytes = substr($bytes, 2);
            }

            // A trailing odd byte cannot be a UTF-16 code unit.
            if (strlen($bytes) % 2 === 1) {
                $bytes = substr($bytes, 0, -1);
            }

            return mb_check_encoding($bytes, $encoding) ? mb_convert_encoding($bytes, 'UTF-8', $encoding) : null;
        }

        if (isset(CodePages::TABLES[$encoding])) {
            $map = [];

            foreach (CodePages::TABLES[$encoding] as $offset => $char) {
                if ($char === null) {
                    if (str_contains($bytes, chr(0x80 + $offset))) {
                        return null;
                    }

                    continue;
                }

                $map[chr(0x80 + $offset)] = $char;
            }

            return strtr($bytes, $map);
        }

        if (in_array($encoding, ['Windows-1251', 'Windows-1252'], true)) {
            return (string) @mb_convert_encoding($bytes, 'UTF-8', $encoding);
        }

        return mb_check_encoding($bytes, $encoding) ? (string) mb_convert_encoding($bytes, 'UTF-8', $encoding) : null;
    }

    /**
     * UTF-16 from its byte order mark, or from the zero bytes that every
     * ASCII character carries in UTF-16. Real CSV text never contains them.
     */
    public static function detectUtf16(string $sample): ?string
    {
        if (str_starts_with($sample, "\xFF\xFE")) {
            return 'UTF-16LE';
        }

        if (str_starts_with($sample, "\xFE\xFF")) {
            return 'UTF-16BE';
        }

        $head = substr($sample, 0, 1024);
        $length = strlen($head);

        if ($length < 2 || substr_count($head, "\0") * 4 < $length) {
            return null;
        }

        $even = 0;
        $odd = 0;

        for ($i = 0; $i < $length; $i++) {
            if ($head[$i] === "\0") {
                $i % 2 === 0 ? $even++ : $odd++;
            }
        }

        return $even > $odd ? 'UTF-16BE' : 'UTF-16LE';
    }

    /**
     * Lowercase non-ASCII letters of each language that uses a Latin code
     * page. A Latin hint is only taken when the decoded letters belong to the
     * app's language: a Spanish file read as Windows-1250 in a Polish app
     * would turn "Muñoz" into "Muńoz" and "Crème" into "Crčme", and "é", "è"
     * and "č" are not Polish.
     *
     * @var array<string, string>
     */
    public const ALPHABETS = [
        'pl' => 'ąćęłńóśźż',
        'cs' => 'áčďéěíňóřšťúůýž',
        'sk' => 'áäčďéíĺľňóôŕšťúýž',
        'sl' => 'čšžćđ',
        'hr' => 'čćđšž', 'bs' => 'čćđšž', 'sr_latn' => 'čćđšž', 'sr' => 'čćđšž',
        'hu' => 'áéíóöőúüű',
        'ro' => 'ăâîșşțţ',
        'sq' => 'çë',
        'tr' => 'çğıöşüâîû',
        'az' => 'çğıöşüə',
        'et' => 'äöõüšž',
        'lv' => 'āčēģīķļņšūž',
        'lt' => 'ąčęėįšųūž',
    ];

    /**
     * The code page of the app's locale, if the sample really reads as text
     * in that language. The locale is the user's interface language, not the
     * file's origin, so the evidence has to be strong: a false positive
     * silently changes imported values.
     */
    public static function hint(string $sample, ?string $locale): ?string
    {
        $encoding = self::forLocale($locale);

        if ($encoding === null) {
            return null;
        }

        $text = self::decode($sample, $encoding);

        if ($text === null || self::anomalies($text) > 0) {
            return null;
        }

        $script = self::SCRIPTS[$encoding];

        if ($script === 'Latin') {
            $language = strtolower(str_replace('-', '_', (string) $locale));
            $alphabet = self::ALPHABETS[$language] ?? self::ALPHABETS[explode('_', $language)[0]] ?? null;

            return $alphabet !== null && self::latinEvidence($sample, $encoding, $alphabet) ? $encoding : null;
        }

        return self::inRuns($text) && self::scriptWords($text, $script) >= 2 ? $encoding : null;
    }

    /**
     * Lowercase non-ASCII letters of the Western European languages whose
     * files arrive as Windows-1252.
     */
    protected const WESTERN_ALPHABETS = [
        // French "æ" is left out: it is rare in French, and it is how "ć"
        // reads in Windows-1252, which Croatian and Polish files need.
        // Icelandic is left out too: "þ", "ð" and "ý" are how Turkish "ş",
        // "ğ" and "ı" read, so it would stop Turkish detection entirely.
        'áéíóúüñ', 'àâçéèêëîïôœùûüÿ', 'äöüß', 'àèéìíîòóù', 'àáâãçéêíóôõú',
        'áéèëïóöü', 'àçéèíïòóú', 'åäöé', 'æøåé', 'äöåšž',
    ];

    /** @var array<string, array<string, array{0: string, 1: string}>> */
    protected static array $pairs = [];

    /**
     * Only bytes that decode differently in the code page and in Windows-1252
     * can tell them apart: "é" and "ü" are the same in both, so "Nestlé" in a
     * Polish file proves nothing either way. Those bytes, where they sit
     * inside a word, must decode to letters of the app's language, and read
     * as Windows-1252 they must not make sense in a Western language on their
     * own: "Muñoz" or "Hélène" stay Western even though "ń" and "č" are
     * Polish and Slovak letters.
     */
    protected static function latinEvidence(string $sample, string $encoding, string $alphabet): bool
    {
        $allowed = mb_str_split($alphabet);
        $evidence = 0;
        $western = [];
        $length = strlen($sample);

        for ($i = 0; $i < $length; $i++) {
            $byte = $sample[$i];

            if (ord($byte) < 0x80) {
                continue;
            }

            [$hinted, $windows] = self::$pairs[$encoding][$byte] ??= [
                mb_strtolower((string) self::decode($byte, $encoding)),
                mb_strtolower((string) @mb_convert_encoding($byte, 'UTF-8', 'Windows-1252')),
            ];

            if ($hinted === $windows) {
                continue;
            }

            $prev = $i > 0 ? $sample[$i - 1] : ' ';
            $next = $i + 1 < $length ? $sample[$i + 1] : ' ';
            $inWord = ctype_alpha($prev) || ctype_alpha($next) || ord($prev) >= 0x80 || ord($next) >= 0x80;

            if (! $inWord) {
                continue;
            }

            // "İ" lowercases to "i" plus a combining dot.
            $letter = preg_replace('/\p{M}/u', '', $hinted);

            if (! in_array($letter, $allowed, true) && preg_match('/^[a-z]$/', $letter) !== 1) {
                return false;
            }

            $evidence++;
            $western[] = $windows;
        }

        if ($evidence === 0) {
            return false;
        }

        foreach (self::WESTERN_ALPHABETS as $letters) {
            if (array_diff(array_unique($western), mb_str_split($letters)) === []) {
                return false;
            }
        }

        return true;
    }

    /**
     * Greek, Hebrew, Arabic, Thai and CJK words decode to runs of non-ASCII
     * characters, while the stray bytes of a Western file decode to isolated
     * ones: "£3", "200°C" and "Café" become one odd character each.
     */
    protected static function inRuns(string $text): bool
    {
        preg_match_all('/[^\x00-\x7F]+/u', preg_replace('/\p{M}/u', '', $text), $runs);
        $chars = 0;
        $isolated = 0;

        foreach ($runs[0] as $run) {
            $length = mb_strlen($run);
            $chars += $length;
            $isolated += $length === 1 ? 1 : 0;
        }

        return $chars >= 4 && $isolated * 5 <= $chars;
    }

    /** Words of at least two letters in the script. */
    protected static function scriptWords(string $text, string $script): int
    {
        $class = $script === 'CJK' ? '[\p{Han}\p{Hiragana}\p{Katakana}\p{Hangul}]' : "\\p{{$script}}";

        return preg_match_all('/'.$class.'{2,}/u', preg_replace('/\p{M}/u', '', $text));
    }

    /**
     * Signs of a wrong code page: a word mixing two scripts ("Cafι"), or a
     * symbol inside a word ("³ódź").
     */
    protected static function anomalies(string $text): int
    {
        $anomalies = 0;

        foreach (preg_split('/[\x00-\x2F\x3A-\x40\x5B-\x60\x7B-\x7F]+/', $text, flags: PREG_SPLIT_NO_EMPTY) as $token) {
            if (preg_match('/[^\x00-\x7F]/', $token) !== 1) {
                continue;
            }

            $scripts = 0;

            foreach (['\p{Latin}', '\p{Greek}', '\p{Cyrillic}', '\p{Hebrew}', '\p{Arabic}', '\p{Thai}', '[\p{Han}\p{Hiragana}\p{Katakana}\p{Hangul}]'] as $class) {
                $scripts += preg_match('/'.$class.'/u', $token);
            }

            if ($scripts > 1 || preg_match('/\p{L}[^\x00-\x7F\p{L}\p{M}\p{N}\p{Zs}]|[^\x00-\x7F\p{L}\p{M}\p{N}\p{Zs}]\p{L}/u', $token) === 1) {
                $anomalies++;
            }
        }

        return $anomalies;
    }
}
