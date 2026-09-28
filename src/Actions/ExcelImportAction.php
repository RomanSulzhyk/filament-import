<?php

namespace RomanSulzhyk\FilamentImport\Actions;

use RomanSulzhyk\FilamentImport\Importing\FailedRowsWriter;
use RomanSulzhyk\FilamentImport\Importing\FillableImporter;
use RomanSulzhyk\FilamentImport\Importing\ImportResult;
use RomanSulzhyk\FilamentImport\Importing\ImportRunner;
use RomanSulzhyk\FilamentImport\Importing\TemplateWriter;
use RomanSulzhyk\FilamentImport\Importing\TooManyRowsForSyncImport;
use RomanSulzhyk\FilamentImport\Mapping\ColumnMatcher;
use RomanSulzhyk\FilamentImport\Reading\Encoding;
use RomanSulzhyk\FilamentImport\Reading\ReaderFactory;
use RomanSulzhyk\FilamentImport\Reading\SpreadsheetReader;
use RomanSulzhyk\FilamentImport\Reading\UnsupportedSpreadsheet;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use LogicException;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

/**
 * Import .xlsx or .csv files into a Filament resource, immediately, with no
 * queue worker.
 *
 * Zero configuration, like the popular pre-v5 import plugins:
 *
 *     ExcelImportAction::make()
 *
 * Or reuse an existing core Filament importer, now without queues:
 *
 *     ExcelImportAction::make()->importer(CustomerImporter::class)
 */
class ExcelImportAction extends Action
{
    /** @var class-string<Importer>|null */
    protected ?string $importer = null;

    /** @var class-string<Model>|null */
    protected ?string $importModel = null;

    /** @var array<string, mixed> */
    protected array $validationRules = [];

    /** @var list<string> */
    protected array $upsertKeys = [];

    /** @var list<string>|null */
    protected ?array $onlyColumns = null;

    /** @var list<string> */
    protected array $exceptColumns = [];

    /**
     * Readers for the current upload, so the file is parsed once per request
     * instead of on every render of the mapping form.
     *
     * @var array<string, SpreadsheetReader|UnsupportedSpreadsheet>
     */
    protected array $readers = [];

    /** @var array<string, string> Local copies of remote uploads. */
    protected array $localCopies = [];

    /** @var array<string, mixed>|Closure */
    protected array | Closure $importerOptions = [];

    protected ?Closure $beforeImport = null;

    protected ?Closure $afterImport = null;

    protected ?int $syncRowLimit = null;

    protected ?string $csvEncoding = null;

    protected bool $hasTemplateDownload = true;

    /** @var array<string, mixed>|Closure */
    protected array | Closure $additionalData = [];

    /** @var array<\Filament\Schemas\Components\Component>|Closure */
    protected array | Closure $optionsFormComponents = [];

    protected int $maxFileSizeKilobytes = 10240;

    /** @var array<string>|Closure|null */
    protected array | Closure | null $acceptedFileTypes = null;

    public static function getDefaultName(): ?string
    {
        return 'excelImport';
    }

    /**
     * Some systems detect an .xlsx as a plain zip, and an old .xls as generic
     * OLE storage. Both are accepted here so the reader can open the .xlsx, or
     * refuse the .xls with a message that says what to do instead.
     */
    public const ACCEPTED_FILE_TYPES = [
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/csv', 'text/x-csv', 'application/csv', 'application/x-csv',
        'text/comma-separated-values', 'text/x-comma-separated-values',
        'text/plain', 'application/vnd.ms-excel', 'text/tab-separated-values',
        'application/zip', 'application/x-zip-compressed',
        'application/x-ole-storage', 'application/CDFV2', 'application/vnd.ms-office',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-import::import.action.label'));
        $this->icon(Heroicon::ArrowUpTray);
        $this->color('gray');
        $this->modalHeading(fn (): string => __('filament-import::import.action.modal_heading', [
            'label' => Str::of(class_basename($this->resolveModel()))->headline()->plural()->toString(),
        ]));
        $this->modalSubmitActionLabel(__('filament-import::import.action.submit'));
        $this->modalDescription(fn (): ?Action => $this->hasTemplateDownload ? $this->getModalAction('downloadTemplate') : null);

        $this->registerModalActions([
            Action::make('downloadTemplate')
                ->label(__('filament-import::import.action.download_template'))
                ->link()
                ->visible(fn (): bool => $this->hasTemplateDownload)
                ->action(function (): BinaryFileResponse {
                    $name = Str::of(class_basename($this->resolveModel()))->kebab()->plural()->append('-template.xlsx')->toString();

                    return response()
                        ->download(TemplateWriter::write($this->getImportColumns()), $name, [
                            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ])
                        ->deleteFileAfterSend();
                }),
        ]);

        $this->schema(function (): array {
            // Resolving the columns here surfaces configuration errors, such as
            // an unsupported tenant placement, when the modal opens rather
            // than after the user has uploaded a file.
            $this->getImportColumns();

            return [
                FileUpload::make('file')
                    ->label(__('filament-import::import.fields.file.label'))
                    ->helperText(__('filament-import::import.fields.file.helper'))
                    ->acceptedFileTypes($this->getAcceptedFileTypes())
                    ->maxSize($this->maxFileSizeKilobytes)
                    ->storeFiles(false)
                    ->visibility('private')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (FileUpload $component, Component $livewire, Set $set, mixed $state): void {
                        // Enforce maxSize() and acceptedFileTypes() before parsing,
                        // as the core ImportAction does, so an oversized or wrong
                        // file is never opened.
                        try {
                            $livewire->validateOnly($component->getStatePath());
                        } catch (ValidationException $exception) {
                            $component->state([]);

                            throw $exception;
                        }

                        try {
                            $reader = $this->readerFor($state, rethrow: true);
                        } catch (UnsupportedSpreadsheet $exception) {
                            $component->state([]);

                            throw ValidationException::withMessages([$component->getStatePath() => $exception->getMessage()]);
                        }

                        $set('columnMap', $reader ? $this->suggestColumnMap($reader) : []);
                    }),
                Fieldset::make(__('filament-import::import.steps.mapping'))
                    ->columns(1)
                    ->inlineLabel()
                    ->statePath('columnMap')
                    ->visible(fn (Get $get): bool => $this->readerFor($get('file')) !== null)
                    ->schema(function (Get $get): array {
                        $reader = $this->readerFor($get('file'));

                        if (! $reader) {
                            return [];
                        }

                        $headers = $reader->headers();
                        $options = array_combine($headers, $headers);

                        return array_map(
                            fn (ImportColumn $column) => $column->getSelect()
                                ->options($options)
                                ->placeholder(__('filament-import::import.fields.mapping.placeholder')),
                            $this->getImportColumns(),
                        );
                    }),
                ...$this->getOptionsFormComponents(),
            ];
        });

        $this->action(function (array $data, Component $livewire): void {
            $this->runImport($data, $livewire);
        });
    }

    /**
     * @param  class-string<Importer>  $importer
     */
    public function importer(?string $importer): static
    {
        $this->importer = $importer;

        return $this;
    }

    /**
     * The model to import into when no importer class is given. Defaults to
     * the model of the resource or table the action sits on.
     *
     * @param  class-string<Model>|null  $model
     */
    public function importModel(?string $model): static
    {
        $this->importModel = $model;

        return $this;
    }

    /**
     * Validation rules for the zero-config importer, keyed by attribute.
     * Same signature as the pre-v5 eighty9nine plugin.
     *
     * @param  array<string, mixed>  $rules
     */
    public function validateUsing(array $rules): static
    {
        $this->validationRules = $rules;

        return $this;
    }

    /**
     * Zero-config mode: update the record whose values in these columns match
     * the row, instead of always creating one. With an importer class, put the
     * same logic in its resolveRecord() instead.
     *
     * @param  list<string>|string  $keys
     */
    public function upsertBy(array | string $keys): static
    {
        $this->upsertKeys = (array) $keys;

        return $this;
    }

    /**
     * Zero-config mode: import only these attributes. They must also be in
     * `$fillable`, so this can narrow what users may write but never widen it.
     *
     * @param  list<string>  $attributes
     */
    public function importColumns(array $attributes): static
    {
        $this->onlyColumns = array_values($attributes);

        return $this;
    }

    /**
     * Zero-config mode: never offer these attributes, for example ownership or
     * permission columns that are fillable for other reasons.
     *
     * @param  list<string>  $attributes
     */
    public function exceptColumns(array $attributes): static
    {
        $this->exceptColumns = array_values($attributes);

        return $this;
    }

    /**
     * @param  array<string, mixed>|Closure  $options  Passed to the importer as $this->options.
     */
    public function importerOptions(array | Closure $options): static
    {
        $this->importerOptions = $options;

        return $this;
    }

    /**
     * Runs before any row is imported. Receives $data, $livewire and $action,
     * like the pre-v5 plugin. Throw or call $action->halt() to stop.
     */
    public function beforeImport(?Closure $callback): static
    {
        $this->beforeImport = $callback;

        return $this;
    }

    /**
     * Runs after the import. Receives $data, $livewire, $action and $result.
     */
    public function afterImport(?Closure $callback): static
    {
        $this->afterImport = $callback;

        return $this;
    }

    public function syncRowLimit(?int $rows): static
    {
        $this->syncRowLimit = $rows;

        return $this;
    }

    /**
     * Read every CSV in this encoding (for example "Windows-1250" or
     * "Shift_JIS") instead of detecting it. Detection covers UTF-8, UTF-16,
     * Windows-1251 and Windows-1252 in any app, and the code page of the
     * app's locale; set this when files come from elsewhere.
     */
    public function csvEncoding(?string $encoding): static
    {
        $this->csvEncoding = $encoding === null ? null : Encoding::canonical($encoding);

        return $this;
    }

    /**
     * Values written to every imported record in zero-config mode, such as a
     * parent key or a source tag. A closure receives the modal's form data as
     * $data, including optionsFormComponents() fields. These attributes are
     * never offered for mapping, and a file column can never override them.
     *
     * @param  array<string, mixed>|Closure  $data
     */
    public function additionalData(array | Closure $data): static
    {
        $this->additionalData = $data;

        return $this;
    }

    /**
     * Extra fields shown in the import modal, below the file and the column
     * matching. Their values reach the importer as $this->options, merged over
     * importerOptions(), exactly as the core ImportAction does, and the
     * additionalData() closure as $data. An importer class's own
     * getOptionsFormComponents() are shown automatically.
     *
     * @param  array<\Filament\Schemas\Components\Component>|Closure  $components
     */
    public function optionsFormComponents(array | Closure $components): static
    {
        $this->optionsFormComponents = $components;

        return $this;
    }

    /**
     * @return array<\Filament\Schemas\Components\Component>
     */
    public function getOptionsFormComponents(): array
    {
        return [
            ...($this->importer !== null ? $this->importer::getOptionsFormComponents() : []),
            ...$this->evaluate($this->optionsFormComponents),
        ];
    }

    /**
     * Show the "Download template" link in the modal: an .xlsx with one header
     * per import column and the columns' examples. On by default.
     */
    public function templateDownload(bool $condition = true): static
    {
        $this->hasTemplateDownload = $condition;

        return $this;
    }

    public function maxFileSize(int $kilobytes): static
    {
        $this->maxFileSizeKilobytes = $kilobytes;

        return $this;
    }

    /**
     * Replace the MIME types the upload accepts, for systems that label CSV
     * files differently. Extend the defaults with
     * [...ExcelImportAction::ACCEPTED_FILE_TYPES, 'application/octet-stream'].
     * The reader still decides from the file's contents and extension, so an
     * accepted type never makes an unreadable file importable.
     *
     * @param  array<string>|Closure  $types
     */
    public function acceptedFileTypes(array | Closure $types): static
    {
        $this->acceptedFileTypes = $types;

        return $this;
    }

    /**
     * @return array<string>
     */
    public function getAcceptedFileTypes(): array
    {
        return array_values($this->evaluate($this->acceptedFileTypes) ?? static::ACCEPTED_FILE_TYPES);
    }

    /**
     * @return array<ImportColumn>
     */
    public function getImportColumns(): array
    {
        if ($this->importer !== null) {
            if ($this->validationRules !== [] || $this->upsertKeys !== [] || $this->onlyColumns !== null || $this->exceptColumns !== [] || $this->additionalData !== []) {
                throw new LogicException('validateUsing(), upsertBy(), importColumns(), exceptColumns() and additionalData() configure the zero-config importer and have no effect together with ->importer(). Put the rules, resolveRecord(), columns and fixed values in the importer class instead; form fields from optionsFormComponents() reach it as $this->options.');
            }

            return $this->importer::getColumns();
        }

        if (is_array($this->additionalData)) {
            $this->assertNoTenantKey(array_keys($this->additionalData));
        }

        return FillableImporter::columnsFor(
            $this->resolveModel(),
            $this->validationRules,
            $this->upsertKeys,
            $this->onlyColumns,
            [...$this->exceptColumns, ...$this->tenantOwnershipColumns(), ...(is_array($this->additionalData) ? array_keys($this->additionalData) : [])],
        );
    }

    /**
     * Fixed values are written after Filament assigns the tenant, so a
     * tenant key among them would move records into another tenant.
     *
     * @param  list<string>  $keys
     */
    protected function assertNoTenantKey(array $keys): void
    {
        $tenantKeys = array_intersect($keys, $this->tenantOwnershipColumns());

        if ($tenantKeys !== []) {
            throw new LogicException(sprintf(
                'additionalData() cannot set the tenant ownership key [%s]. Filament assigns the tenant itself; a fixed value would move records into another tenant.',
                implode(', ', $tenantKeys),
            ));
        }
    }

    /**
     * On a tenant-scoped resource, the ownership key must never come from the
     * file. Filament re-associates the tenant only when a record is created,
     * so with upsertBy() an imported value would move existing records into
     * another tenant.
     *
     * @return list<string>
     */
    protected function tenantOwnershipColumns(): array
    {
        if (! Filament::hasTenancy()) {
            return [];
        }

        try {
            $livewire = $this->getLivewire();
        } catch (Throwable) {
            return [];
        }

        $onRelationManager = $livewire instanceof RelationManager;

        try {
            $resource = match (true) {
                $onRelationManager => $livewire->getPageClass()::getResource(),
                method_exists($livewire, 'getResource') => $livewire::getResource(),
                default => null,
            };
        } catch (Throwable) {
            $resource = null;
        }

        if ($resource === null || ! $resource::isScopedToTenant()) {
            return [];
        }

        $model = $this->resolveModel();

        // Filament assigns the tenant only when it creates the resource's own
        // model on the resource's own pages; its observer is registered for
        // that exact class. Rows imported anywhere else, including relation
        // managers, relation pages, other models and subclasses, would get no
        // tenant, or a tenant or parent key taken from the file. Refuse unless
        // the developer has listed the columns and so taken responsibility.
        if ($onRelationManager || $model !== $resource::getModel()) {
            if ($this->onlyColumns === null) {
                throw new LogicException(sprintf(
                    'ExcelImportAction imports [%s] %s of the tenant-scoped resource [%s]. Zero-config mode cannot keep those rows inside the current tenant. List the columns with ->importColumns([...]) and assign the tenant yourself, or use ->importer() with an importer that assigns it.',
                    $model, $onRelationManager ? 'in a relation manager' : 'on a page', $resource,
                ));
            }

            try {
                $relationship = $resource::getTenantOwnershipRelationship(new $model);
            } catch (Throwable) {
                return [];
            }
        } else {
            $relationship = $resource::getTenantOwnershipRelationship(new $model);
        }

        return match (true) {
            $relationship instanceof \Illuminate\Database\Eloquent\Relations\MorphTo => [$relationship->getForeignKeyName(), $relationship->getMorphType()],
            $relationship instanceof \Illuminate\Database\Eloquent\Relations\BelongsTo => [$relationship->getForeignKeyName()],
            default => [],
        };
    }

    /**
     * @return class-string<Model>
     */
    public function resolveModel(): string
    {
        if ($this->importer !== null) {
            return $this->importer::getModel();
        }

        if ($this->importModel !== null) {
            return $this->importModel;
        }

        $model = $this->getModel();

        if ($model === null) {
            throw new \LogicException('ExcelImportAction could not determine a model. Call ->importModel(Model::class) or ->importer(Importer::class).');
        }

        return $model;
    }

    /**
     * @return array<string, string|null>
     */
    protected function suggestColumnMap(SpreadsheetReader $reader): array
    {
        $samples = [];

        foreach ($reader->rows() as $row) {
            $samples[] = $row;

            if (count($samples) >= 5) {
                break;
            }
        }

        return app(ColumnMatcher::class)->match($reader->headers(), $this->getImportColumns(), $samples);
    }

    protected function readerFor(mixed $state, bool $rethrow = false): ?SpreadsheetReader
    {
        if (is_array($state)) {
            $state = reset($state) ?: null;
        }

        if (! $state instanceof TemporaryUploadedFile) {
            return null;
        }

        $key = $state->getFilename();

        if (! array_key_exists($key, $this->readers)) {
            try {
                $reader = ReaderFactory::make($this->localPath($state), $state->getClientOriginalName(), $this->csvEncoding);

                $this->readers[$key] = $reader->headers() === []
                    ? new UnsupportedSpreadsheet(__('filament-import::import.errors.no_headers'))
                    : $reader;
            } catch (UnsupportedSpreadsheet $exception) {
                $this->readers[$key] = $exception;
            } catch (Throwable $exception) {
                report($exception);

                $this->readers[$key] = new UnsupportedSpreadsheet(__('filament-import::import.errors.unreadable'));
            }
        }

        $result = $this->readers[$key];

        if ($result instanceof UnsupportedSpreadsheet) {
            if ($rethrow) {
                throw $result;
            }

            return null;
        }

        return $result;
    }

    /**
     * Livewire keeps uploads on its temporary disk, which may be S3. The
     * readers need a local path, so a remote upload is copied down once per
     * action and deleted when the request terminates. Laravel runs terminating
     * callbacks per request under Octane too, unlike shutdown functions.
     */
    protected function localPath(TemporaryUploadedFile $file): string
    {
        $path = $file->getRealPath();

        if (is_string($path) && $path !== '' && is_readable($path)) {
            return $path;
        }

        $key = $file->getFilename();

        if (isset($this->localCopies[$key]) && is_readable($this->localCopies[$key])) {
            return $this->localCopies[$key];
        }

        $local = tempnam(sys_get_temp_dir(), 'filament-import-');
        file_put_contents($local, $file->get());
        $this->localCopies[$key] = $local;

        app()->terminating(static function () use ($local): void {
            if (is_file($local)) {
                @unlink($local);
            }
        });

        return $local;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function runImport(array $data, Component $livewire): void
    {
        try {
            $reader = $this->readerFor($data['file'] ?? null, rethrow: true);
        } catch (UnsupportedSpreadsheet $exception) {
            $reader = null;
            $reason = $exception->getMessage();
        }

        if (! $reader) {
            Notification::make()
                ->title(__('filament-import::import.notifications.failed.title'))
                ->body($reason ?? __('filament-import::import.errors.unreadable'))
                ->danger()
                ->send();

            $this->halt();
        }

        $file = is_array($data['file']) ? reset($data['file']) : $data['file'];

        if ($this->beforeImport) {
            $this->evaluate($this->beforeImport, ['data' => $data, 'livewire' => $livewire, 'excelImportAction' => $this]);
        }

        $model = $this->resolveModel();
        $fixedValues = $this->importer === null ? (array) $this->evaluate($this->additionalData, ['data' => $data]) : [];
        $this->assertNoTenantKey(array_keys($fixedValues));

        $runner = new ImportRunner(
            $reader,
            $this->makeImporterFactory($fixedValues),
            // A fixed value always wins, so no file column may feed it.
            array_diff_key($data['columnMap'] ?? [], $fixedValues),
            array_merge(
                (array) $this->evaluate($this->importerOptions),
                Arr::except($data, ['file', 'columnMap']),
            ),
            $this->syncRowLimit,
            (new $model)->getConnectionName(),
        );

        try {
            $result = $runner->run([
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $file->getRealPath() ?: $file->getFilename(),
                'user_id' => auth()->guard($this->authGuard())->id(),
            ]);
        } catch (TooManyRowsForSyncImport $exception) {
            Notification::make()
                ->title(__('filament-import::import.notifications.failed.title'))
                ->body($exception->getMessage())
                ->danger()
                ->send();

            $this->halt();
        }

        $this->notifyCompleted($reader, $result);

        if ($this->afterImport) {
            $this->evaluate($this->afterImport, [
                'data' => $data,
                'livewire' => $livewire,
                'excelImportAction' => $this,
                'result' => $result,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $fixedValues
     * @return class-string<Importer>|Closure(Import, array<string, string>, array<string, mixed>): Importer
     */
    protected function makeImporterFactory(array $fixedValues = []): string | Closure
    {
        if ($this->importer !== null) {
            return $this->importer;
        }

        $model = $this->resolveModel();
        $columns = $this->getImportColumns();
        $upsertKeys = $this->upsertKeys;

        $columns = array_values(array_filter($columns, fn (ImportColumn $column) => ! array_key_exists($column->getName(), $fixedValues)));

        return fn (Import $import, array $columnMap, array $options): Importer => (new FillableImporter($import, $columnMap, $options))->using($model, $columns, $upsertKeys, $fixedValues);
    }

    /**
     * The guard the current panel authenticates with, not the app default.
     */
    protected function authGuard(): string
    {
        return Filament::getCurrentPanel()?->getAuthGuard() ?? (string) config('auth.defaults.guard', 'web');
    }

    protected function notifyCompleted(SpreadsheetReader $reader, ImportResult $result): void
    {
        $notification = Notification::make()
            ->title(__('filament-import::import.notifications.completed.title'))
            ->body(__(
                $result->skipped > 0
                    ? 'filament-import::import.notifications.completed.body_with_skipped'
                    : 'filament-import::import.notifications.completed.body',
                [
                    'created' => number_format($result->created),
                    'updated' => number_format($result->updated),
                    'skipped' => number_format($result->skipped),
                    'failed' => number_format($result->failed()),
                ],
            ));

        if ($result->failed() === 0) {
            $notification->success()->send();

            return;
        }

        $guard = $this->authGuard();
        $userId = auth()->guard($guard)->id();

        // Without an authenticated user the link could not be restricted to
        // anyone, so no report is written and no link is offered.
        if ($userId === null) {
            $notification->warning()->send();

            return;
        }

        $path = FailedRowsWriter::write($reader->headers(), $result->failures);

        $notification
            ->color($result->succeeded() > 0 ? 'warning' : 'danger')
            ->icon($result->succeeded() > 0 ? Heroicon::OutlinedExclamationTriangle : Heroicon::OutlinedXCircle)
            ->persistent()
            ->actions([
                Action::make('downloadFailedRows')
                    ->label(__('filament-import::import.notifications.download_failures'))
                    ->url(URL::temporarySignedRoute('filament-import.failed-rows', now()->addMinutes(FailedRowsWriter::ttlMinutes()), [
                        'file' => basename($path),
                        'guard' => $guard,
                        'user' => $userId,
                    ]), shouldOpenInNewTab: true)
                    ->markAsRead(),
            ])
            ->send();
    }
}
