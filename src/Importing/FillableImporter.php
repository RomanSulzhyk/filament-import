<?php

namespace RomanSulzhyk\FilamentImport\Importing;

use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Database\Eloquent\Model;
use LogicException;

/**
 * The zero-configuration importer: one column per importable attribute of the
 * model, like the popular pre-v5 import plugins. It is a real Filament
 * Importer, so validation, casting and lifecycle hooks behave exactly as in
 * the core importer. Columns are passed per instance instead of through
 * static state, so two imports in one request cannot interfere.
 */
class FillableImporter extends Importer
{
    /** @var class-string<Model> */
    protected string $modelClass;

    /** @var array<ImportColumn> */
    protected array $columns = [];

    /** @var list<string> */
    protected array $upsertKeys = [];

    /**
     * Every attribute in `$fillable` and not in `$hidden`, optionally narrowed
     * with $only and $except. A column whose rules include `required`, and
     * every upsert key, must be mapped: otherwise leaving it on "Do not import"
     * would silently skip its validation or turn every upsert into an insert.
     *
     * @param  class-string<Model>  $modelClass
     * @param  array<string, mixed>  $rules  Attribute => validation rules.
     * @param  list<string>  $upsertKeys
     * @param  list<string>|null  $only
     * @param  list<string>  $except
     * @return array<ImportColumn>
     */
    public static function columnsFor(string $modelClass, array $rules = [], array $upsertKeys = [], ?array $only = null, array $except = []): array
    {
        $model = new $modelClass;
        $attributes = array_values(array_diff($model->getFillable(), $model->getHidden()));

        if ($only !== null) {
            $attributes = array_values(array_intersect($attributes, $only));
        }

        $attributes = array_values(array_diff($attributes, $except));

        if ($attributes === []) {
            throw new LogicException(sprintf(
                'ExcelImportAction has no columns to import into %s. Zero-config mode reads $fillable, which is empty or fully excluded (a model using $guarded = [] has no $fillable). Declare $fillable, call ->importColumns([...]), or pass an importer class with ->importer().',
                $modelClass,
            ));
        }

        return array_map(function (string $attribute) use ($rules, $upsertKeys): ImportColumn {
            $columnRules = $rules[$attribute] ?? [];
            $columnRules = is_string($columnRules) ? explode('|', $columnRules) : (array) $columnRules;

            return ImportColumn::make($attribute)
                ->rules($columnRules)
                ->requiredMapping(in_array($attribute, $upsertKeys, true) || in_array('required', $columnRules, true));
        }, $attributes);
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @param  array<ImportColumn>  $columns
     * @param  list<string>  $upsertKeys
     */
    public function using(string $modelClass, array $columns, array $upsertKeys = []): static
    {
        $this->modelClass = $modelClass;
        $this->columns = $columns;
        $this->upsertKeys = array_values($upsertKeys);
        unset($this->cachedColumns);

        return $this;
    }

    public function getCachedColumns(): array
    {
        return $this->cachedColumns ??= array_map(
            fn (ImportColumn $column) => $column->importer($this),
            $this->columns,
        );
    }

    public static function getColumns(): array
    {
        return [];
    }

    /**
     * Without upsert keys every row creates a record, like the pre-v5 plugins.
     * With keys, a row updates the record whose key values match, or creates
     * one. A row whose key is blank never matches an existing record, so it is
     * created and left to validation: an empty key must not update whichever
     * record happens to have an empty value in that column.
     */
    public function resolveRecord(): ?Model
    {
        if ($this->upsertKeys === []) {
            return new $this->modelClass;
        }

        $match = [];

        foreach ($this->upsertKeys as $key) {
            $value = $this->data[$key] ?? null;

            if (blank($value)) {
                return new $this->modelClass;
            }

            $match[$key] = $value;
        }

        return $this->modelClass::query()->where($match)->first() ?? new $this->modelClass;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        return '';
    }
}
