<?php

use Illuminate\Support\Facades\Blade;
use RomanSulzhyk\FilamentImport\Actions\ExcelImportAction;

$skillPath = __DIR__.'/../../resources/boost/skills/filament-import/SKILL.md';

it('ships a Boost skill with the frontmatter Boost requires', function () use ($skillPath) {
    $skill = file_get_contents($skillPath);

    expect($skill)->toStartWith("---\n")
        ->and(preg_match('/\A---\n(.*?)\n---\n/s', $skill, $frontmatter))->toBe(1)
        ->and($frontmatter[1])->toMatch('/^name: filament-import$/m')
        ->and($frontmatter[1])->toMatch('/^description: \S.{40,}$/m');
});

it('only shows methods in the skill code samples that the action really has', function () use ($skillPath) {
    $skill = file_get_contents($skillPath);
    preg_match_all('/```php\n(.*?)```/s', $skill, $blocks);
    $code = implode("\n", $blocks[1]);
    preg_match_all('/->([a-zA-Z]+)\(/', $code, $calls);

    // Calls on other objects in the samples: Filament Select, Livewire testing and Pest.
    $notOnTheAction = ['options', 'required', 'mountAction', 'fillForm', 'callMountedAction',
        'assertHasNoFormErrors', 'assertNotified', 'toBeTrue', 'exists', 'createWithContent', 'failed', 'halt'];

    $missing = collect($calls[1])->unique()
        ->reject(fn (string $method) => in_array($method, $notOnTheAction, true))
        ->reject(fn (string $method) => method_exists(ExcelImportAction::class, $method))
        ->values()->all();

    expect($missing)->toBe([]);
});

it('ships a Boost guideline that renders as Blade', function () {
    $html = Blade::render(file_get_contents(__DIR__.'/../../resources/boost/guidelines/core.blade.php'));

    expect($html)->toContain('RomanSulzhyk\FilamentImport\Actions\ExcelImportAction');
});

it('names only real methods and config keys in context7.json', function () {
    $context = json_decode(file_get_contents(__DIR__.'/../../context7.json'), true, flags: JSON_THROW_ON_ERROR);

    // Context7 rejects a description over 200 characters.
    expect(mb_strlen($context['description']))->toBeLessThanOrEqual(200)
        ->and(mb_strlen($context['projectTitle']))->toBeLessThanOrEqual(100)
        ->and($context['rules'])->not->toBeEmpty()
        ->and(method_exists(ExcelImportAction::class, 'syncRowLimit'))->toBeTrue()
        ->and(method_exists(ExcelImportAction::class, 'importer'))->toBeTrue()
        ->and(config('filament-import.sync_row_limit'))->toBe(2000);
});
