<?php

use RomanSulzhyk\FilamentImport\Actions\ExcelImportAction;
use RomanSulzhyk\FilamentImport\Reading\CodePages;
use RomanSulzhyk\FilamentImport\Reading\CsvReader;
use RomanSulzhyk\FilamentImport\Reading\Encoding;
use RomanSulzhyk\FilamentImport\Reading\ReaderFactory;

function encCsv(string $bytes): string
{
    $path = tempnam(sys_get_temp_dir(), 'fi-enc').'.csv';
    file_put_contents($path, $bytes);

    return $path;
}

/** Encode UTF-8 text in a code page, using the bundled table or mbstring. */
function encBytes(string $text, string $encoding): string
{
    if (isset(CodePages::TABLES[$encoding])) {
        $map = [];

        foreach (CodePages::TABLES[$encoding] as $offset => $char) {
            if ($char !== null) {
                $map[$char] = chr(0x80 + $offset);
            }
        }

        return strtr($text, $map);
    }

    return mb_convert_encoding($text, $encoding, 'UTF-8');
}

function encFirstRow(string $path, ?string $encoding = null): array
{
    return iterator_to_array((new CsvReader($path, encoding: $encoding))->rows(), false)[0];
}

it('reads UTF-16 as Excel saves "Unicode Text", with a BOM and tabs', function () {
    $path = encCsv("\xFF\xFE".mb_convert_encoding("Місто\tЦіна\r\nЛьвів\t12,50\r\n", 'UTF-16LE', 'UTF-8'));

    expect(CsvReader::detectEncoding($path))->toBe('UTF-16LE')
        ->and((new CsvReader($path))->headers())->toBe(['Місто', 'Ціна'])
        ->and(encFirstRow($path))->toBe(['Місто' => 'Львів', 'Ціна' => '12,50']);
});

it('reads UTF-16 without a byte order mark, in both byte orders', function (string $order) {
    $path = encCsv(mb_convert_encoding("name,city\nZoë,Kraków\n", $order, 'UTF-8'));

    expect(CsvReader::detectEncoding($path))->toBe($order)
        ->and(encFirstRow($path))->toBe(['name' => 'Zoë', 'city' => 'Kraków']);
})->with(['UTF-16LE', 'UTF-16BE']);

it('reads the Excel code page of the app locale', function (string $locale, string $encoding, string $text, string $column, string $expected) {
    app()->setLocale($locale);
    $path = encCsv(encBytes($text, $encoding));

    expect(CsvReader::detectEncoding($path, $locale))->toBe($encoding)
        ->and(encFirstRow($path)[$column])->toBe($expected);
})->with([
    'Polish' => ['pl', 'Windows-1250', "Imię;Miasto\nŁucja;Łódź\nŚwiętosław;Gdańsk\n", 'Miasto', 'Łódź'],
    'Czech' => ['cs', 'Windows-1250', "Jméno;Město\nJiří;Ústí nad Labem\nŘehoř;Plzeň\n", 'Město', 'Ústí nad Labem'],
    'Hungarian' => ['hu', 'Windows-1250', "Név;Város\nŐsz Ödön;Győr\nSzűcs Anna;Fűzfő\n", 'Város', 'Győr'],
    'Greek' => ['el', 'Windows-1253', "Όνομα;Πόλη\nΓιώργος;Αθήνα\n", 'Πόλη', 'Αθήνα'],
    'Turkish' => ['tr', 'Windows-1254', "Ad;Şehir\nAyşe;İstanbul\n", 'Şehir', 'İstanbul'],
    'Hebrew' => ['he', 'Windows-1255', "שם,עיר\nדנה,חיפה\n", 'עיר', 'חיפה'],
    'Arabic' => ['ar', 'Windows-1256', "الاسم,المدينة\nسارة,القاهرة\n", 'المدينة', 'القاهرة'],
    'Lithuanian' => ['lt', 'Windows-1257', "Vardas;Miestas\nŽydrūnas;Šiauliai\nGintarė;Klaipėda\n", 'Miestas', 'Šiauliai'],
    'Thai' => ['th', 'Windows-874', "ชื่อ,เมือง\nสมชาย,เชียงใหม่\n", 'เมือง', 'เชียงใหม่'],
    'Japanese' => ['ja', 'CP932', "氏名,電話番号\n山田太郎,03-1234-5678\n", '氏名', '山田太郎'],
    'Simplified Chinese' => ['zh_CN', 'CP936', "姓名,城市\n王伟,北京\n", '城市', '北京'],
    'Traditional Chinese' => ['zh_TW', 'CP950', "姓名,城市\n陳大文,臺北\n", '城市', '臺北'],
    'Korean' => ['ko', 'CP949', "이름,도시\n김민수,서울\n", '도시', '서울'],
]);

it('keeps reading Western and Cyrillic files correctly whatever the locale', function (string $locale, string $encoding, string $text, string $column, string $expected) {
    app()->setLocale($locale);
    $path = encCsv(mb_convert_encoding($text, $encoding, 'UTF-8'));

    expect(CsvReader::detectEncoding($path, $locale))->toBe($encoding)
        ->and(encFirstRow($path)[$column])->toBe($expected);
})->with([
    'French file, Greek app' => ['el', 'Windows-1252', "nom;ville\nCafé Crème;Besançon\n", 'ville', 'Besançon'],
    'German file, Hebrew app' => ['he', 'Windows-1252', "name;stadt\nJürgen Müller;Köln\n", 'stadt', 'Köln'],
    'Ukrainian file, Polish app' => ['pl', 'Windows-1251', "Ім'я;Місто\nОлена;Київ\n", 'Місто', 'Київ'],
    'Ukrainian file, Japanese app' => ['ja', 'Windows-1251', "Ім'я;Місто\nОлена;Київ\nТарас;Одеса\n", 'Місто', 'Київ'],
    'German file, English app' => ['en', 'Windows-1252', "name;stadt\nJürgen;Köln\n", 'stadt', 'Köln'],
]);

it('lets the developer name the encoding, by action or by config', function () {
    $path = encCsv(encBytes("Imię;Miasto\nŁucja;Łódź\n", 'Windows-1250'));

    expect(encFirstRow($path, 'cp1250')['Miasto'])->toBe('Łódź')
        ->and(ReaderFactory::make($path, 'x.csv', 'Windows-1250')->headers())->toBe(['Imię', 'Miasto']);

    config()->set('filament-import.csv_encoding', 'Windows-1250');

    expect(encFirstRow($path)['Miasto'])->toBe('Łódź');
});

it('accepts common encoding names and refuses unknown ones', function () {
    expect(Encoding::canonical('Shift_JIS'))->toBe('CP932')
        ->and(Encoding::canonical('big5'))->toBe('CP950')
        ->and(Encoding::canonical('GBK'))->toBe('CP936')
        ->and(Encoding::canonical('euc-kr'))->toBe('CP949')
        ->and(Encoding::canonical('windows-1250'))->toBe('Windows-1250')
        ->and(fn () => ExcelImportAction::make()->csvEncoding('klingon'))->toThrow(InvalidArgumentException::class);
});

it('refuses a file that is not valid in the encoding the developer named', function () {
    // 0x98 is undefined in Windows-1253.
    $path = encCsv("name\n\x98\n");

    expect(fn () => new CsvReader($path, encoding: 'Windows-1253'))
        ->toThrow(\RomanSulzhyk\FilamentImport\Reading\UnsupportedSpreadsheet::class);
});

it('never lets the app locale reinterpret a Western file', function (string $locale, string $text, string $expected) {
    app()->setLocale($locale);
    $path = encCsv(mb_convert_encoding("item,value\n{$text}\n", 'Windows-1252', 'UTF-8'));

    expect(encFirstRow($path)['value'])->toBe($expected);
})->with(function () {
    $cases = [];

    foreach (['pl', 'cs', 'sk', 'hu', 'ro', 'hr', 'sl', 'bs', 'sq', 'sr_Latn', 'lt', 'lv', 'et', 'tr', 'az', 'vi'] as $locale) {
        $cases["Spanish and French, {$locale}"] = [$locale, 'José Muñoz,"España, Crème brûlée"', 'España, Crème brûlée'];
    }

    foreach (['th', 'ja', 'pl', 'ko', 'zh_CN', 'zh_TW', 'el', 'he', 'ar'] as $locale) {
        $cases["pound, {$locale}"] = [$locale, 'Coffee,£3', '£3'];
        $cases["degrees, {$locale}"] = [$locale, 'Oven,200°C', '200°C'];
        $cases["yen, {$locale}"] = [$locale, 'Tea,¥500', '¥500'];
        $cases["section, {$locale}"] = [$locale, 'BGB,§ 823', '§ 823'];
    }

    return $cases;
});

it('still takes the locale code page for a real file with a few foreign names', function () {
    app()->setLocale('cs');
    $path = encCsv(encBytes("Jméno;Město\nJiří Novák;Plzeň\nŘehoř Černý;České Budějovice\n", 'Windows-1250'));

    expect(encFirstRow($path)['Město'])->toBe('Plzeň');
});

it('keeps Western names that happen to use the locale\'s letters', function (string $locale, string $text, string $expected) {
    app()->setLocale($locale);
    $path = encCsv(mb_convert_encoding("item,value\n{$text}\n", 'Windows-1252', 'UTF-8'));

    expect(encFirstRow($path)['value'])->toBe($expected);
})->with([
    'Spanish, Polish app' => ['pl', 'x,"Muñoz, Logroño"', 'Muñoz, Logroño'],
    'French, Slovak app' => ['sk', 'x,"Hélène, Adèle"', 'Hélène, Adèle'],
    'Portuguese, Romanian app' => ['ro', 'x,São Paulo', 'São Paulo'],
    'Portuguese, Vietnamese app' => ['vi', 'x,São Paulo', 'São Paulo'],
]);

it('reads a real Central European file that also has foreign names', function (string $locale, string $text, string $column, string $expected) {
    app()->setLocale($locale);
    $path = encCsv(encBytes($text, 'Windows-1250'));

    expect(encFirstRow($path)[$column])->toBe($expected);
})->with([
    'Polish with Nestlé and Müller' => ['pl', "Firma;Miasto\nNestlé Polska;Łódź\nMüller sp. z o.o.;Gdańsk\n", 'Miasto', 'Łódź'],
    'Czech with Müller' => ['cs', "Jméno;Město\nJiří Müller;Plzeň\nŘehoř Černý;České Budějovice\n", 'Město', 'Plzeň'],
]);

it('pins how ambiguous Central European files are read', function (string $locale, string $text, string $column, string $expected) {
    app()->setLocale($locale);
    $path = encCsv(encBytes($text, 'Windows-1250'));

    expect(encFirstRow($path)[$column])->toBe($expected);
})->with([
    // "ć" reads as "æ", which no Western language pairs with "è", so it decides.
    'Croatian with ć' => ['hr', "Ime;Prezime\nIvan;Kovačević\nAna;Horvatić\n", 'Prezime', 'Kovačević'],
    // Only "č" (read as French "è"): ambiguous, so Windows-1252, as in 1.0.0.
    'Slovak with only č' => ['sk', "Meno;Priezvisko\nJán;Kováč\n", 'Priezvisko', 'Kováè'],
    // Only "ő" (read as Portuguese "õ"): ambiguous.
    'Hungarian with only ő' => ['hu', "Név;Város\nAnna;Győr\n", 'Város', 'Gyõr'],
]);

it('reads an Icelandic Windows-1252 file as Turkish in a Turkish app, a documented trade-off', function () {
    app()->setLocale('tr');
    $path = encCsv(mb_convert_encoding("name,city\nÞórður,Reykjavík\n", 'Windows-1252', 'UTF-8'));

    expect(encFirstRow($path)['name'])->toBe('Şórğur')
        ->and(encFirstRow($path, 'Windows-1252')['name'])->toBe('Þórður');
});
