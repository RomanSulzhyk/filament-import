<?php

use RomanSulzhyk\FilamentImport\Mapping\HeaderNormalizer;
use RomanSulzhyk\FilamentImport\Mapping\HeuristicColumnMatcher;
use Filament\Actions\Imports\ImportColumn;

function columns(string ...$names): array
{
    return array_map(fn ($name) => ImportColumn::make($name), $names);
}

it('normalizes headers written in different styles to the same form', function () {
    expect(HeaderNormalizer::normalize('E-mail Address '))->toBe('e mail address')
        ->and(HeaderNormalizer::normalize('firstName'))->toBe('first name')
        ->and(HeaderNormalizer::normalize('FIRST_NAME'))->toBe('first name')
        ->and(HeaderNormalizer::normalize('Телефон'))->toBe(HeaderNormalizer::normalize('telefon'))
        ->and(HeaderNormalizer::normalize('电话号码'))->toBe('电话号码');
});

it('matches exact, compact and contained headers', function () {
    $map = (new HeuristicColumnMatcher)->match(
        ['FirstName', 'Customer Email', 'Zip Code', 'Notes'],
        columns('first_name', 'email', 'zip_code', 'phone'),
    );

    expect($map)->toBe([
        'first_name' => 'FirstName',
        'email' => 'Customer Email',
        'zip_code' => 'Zip Code',
        'phone' => null,
    ]);
});

it('uses the guesses declared on a core ImportColumn', function () {
    $map = (new HeuristicColumnMatcher)->match(
        ['Signup Date'],
        [ImportColumn::make('joined_at')->guess(['signup date'])],
    );

    expect($map)->toBe(['joined_at' => 'Signup Date']);
});

it('never feeds two columns from the same header, and the better fit wins', function () {
    $map = (new HeuristicColumnMatcher)->match(
        ['email'],
        columns('email', 'backup_email'),
    );

    expect($map)->toBe(['email' => 'email', 'backup_email' => null]);
});

it('ships every translation with the same keys as English', function () {
    $flatten = function (array $array, string $prefix = '') use (&$flatten): array {
        $keys = [];
        foreach ($array as $key => $value) {
            $keys = [...$keys, ...(is_array($value) ? $flatten($value, "{$prefix}{$key}.") : ["{$prefix}{$key}"])];
        }

        return $keys;
    };

    $english = $flatten(require __DIR__ . '/../../resources/lang/en/import.php');

    foreach (glob(__DIR__ . '/../../resources/lang/*/import.php') as $file) {
        expect($flatten(require $file))->toEqualCanonicalizing($english);
    }
});

it('matches headers in every script', function (array $headers) {
    $map = (new HeuristicColumnMatcher)->match($headers, columns('email', 'phone', 'city'));

    expect($map)->toBe(['email' => $headers[0], 'phone' => $headers[1], 'city' => $headers[2]]);
})->with([
    'Japanese' => [['メールアドレス', '電話番号', '市区町村']],
    'Chinese' => [['电子邮箱', '联系电话', '城市']],
    'Korean' => [['이메일 주소', '전화번호', '도시']],
    'Arabic' => [['البريد الإلكتروني', 'رقم الهاتف', 'المدينة']],
    'Hebrew' => [['דוא"ל', 'טלפון', 'עיר']],
    'Hindi' => [['ईमेल', 'फ़ोन नंबर', 'शहर']],
    'Thai' => [['อีเมล', 'เบอร์โทรศัพท์', 'เมือง']],
    'Greek' => [['Ηλεκτρονικό ταχυδρομείο', 'Τηλέφωνο', 'Πόλη']],
    'Japanese full-width' => [['ＥＭＡＩＬ', '電話', '市区町村']],
]);

it('keeps scripts without a reliable transliteration as written', function () {
    expect(HeaderNormalizer::normalize('नाम'))->not->toBe(HeaderNormalizer::normalize('Nama'))
        ->and(HeaderNormalizer::normalize('جوال'))->not->toBe('goal')
        ->and(HeaderNormalizer::compact('โทรศัพท์'))->toBe('โทรศัพท์');
});

it('never puts a full name into a first-name field', function (string $given, string $surname) {
    $m = fn (array $headers, string ...$columns) => (new HeuristicColumnMatcher)->match($headers, columns(...$columns));

    expect($m([$given], 'first_name'))->toBe(['first_name' => null])
        ->and($m([$given], 'name'))->toBe(['name' => $given])
        ->and($m([$given], 'name', 'first_name'))->toBe(['name' => null, 'first_name' => null])
        ->and($m([$given, $surname], 'first_name', 'last_name'))->toBe(['first_name' => $given, 'last_name' => $surname]);
})->with([
    ['Nombre', 'Apellido'],
    ['Nome', 'Sobrenome'],
    ['이름', '성씨'],
    ["Ім'я", 'Прізвище'],
]);

it('matches bilingual headers through either half', function () {
    $map = (new HeuristicColumnMatcher)->match(['Email (メール)', 'Name / 名前', '電話 / Tel.'], columns('email', 'name', 'phone'));

    expect($map)->toBe(['email' => 'Email (メール)', 'name' => 'Name / 名前', 'phone' => '電話 / Tel.']);
});

it('does not transliterate a word that would lose letters', function () {
    expect((new HeuristicColumnMatcher)->match(['SAR'], columns('city')))->toBe(['city' => null])
        ->and(HeaderNormalizer::normalize('Шаҳр'))->toBe('шаҳр');
});

it('leaves ordinary English and trade headers alone', function (string $header, string $column) {
    expect((new HeuristicColumnMatcher)->match([$header], columns($column)))->toBe([$column => null]);
})->with([
    ['Tara', 'country'],
    ['SAR', 'city'],
    ['Gin', 'price'],
    ['Not', 'notes'],
    ['Man', 'country'],
]);
