<?php

namespace RomanSulzhyk\FilamentImport\Importing;

use RomanSulzhyk\FilamentImport\Reading\SpreadsheetReader;
use Closure;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Runs a Filament Importer over every row of a spreadsheet, in-process.
 *
 * Unlike the core ImportAction this needs no queue worker, job batch table or
 * persisted Import record, so it works on any host. Each row runs in its own
 * transaction (a savepoint when nested), so one failing row neither aborts the
 * file nor, on PostgreSQL, poisons the rows after it.
 */
class ImportRunner
{
    /**
     * @param  class-string<Importer>|Closure(Import, array<string, string>, array<string, mixed>): Importer  $importer
     * @param  array<string, string|null>  $columnMap  Column name => file header.
     * @param  array<string, mixed>  $options
     */
    public function __construct(
        protected SpreadsheetReader $reader,
        protected string | Closure $importer,
        protected array $columnMap,
        protected array $options = [],
        protected ?int $syncRowLimit = null,
        protected ?string $connection = null,
    ) {}

    /**
     * @param  array<string, mixed>  $importAttributes  Stored on the in-memory Import (file_name, user_id, ...).
     *
     * @throws TooManyRowsForSyncImport before any row is written.
     */
    public function run(array $importAttributes = []): ImportResult
    {
        $limit = $this->syncRowLimit ?? (int) config('filament-import.sync_row_limit', 2000);
        $total = $this->countRows($limit);

        if ($total > $limit) {
            throw new TooManyRowsForSyncImport($limit);
        }

        $import = new Import;
        $import->forceFill([
            'importer' => is_string($this->importer) ? $this->importer : FillableImporter::class,
            'total_rows' => $total,
            'processed_rows' => 0,
            'successful_rows' => 0,
            ...$importAttributes,
        ]);

        $columnMap = array_filter($this->columnMap, fn ($header) => filled($header));
        $importer = $this->makeImporter($import, $columnMap);
        $result = new ImportResult;

        // The transaction must be on the model's connection, not the default one,
        // or a model stored elsewhere would not be protected at all.
        $database = DB::connection($this->connection ?? $this->modelConnection());

        foreach ($this->reader->rows() as $line => $row) {
            try {
                $database->transaction(fn () => $importer($row));

                $record = $importer->getRecord();

                match (true) {
                    $record === null => $result->skipped++,
                    $record->wasRecentlyCreated => $result->created++,
                    default => $result->updated++,
                };
            } catch (RowImportFailedException $exception) {
                $result->failures[] = new RowFailure($line, $row, [$exception->getMessage()]);
            } catch (ValidationException $exception) {
                $result->failures[] = new RowFailure($line, $row, array_values(array_map(
                    'strval',
                    collect($exception->errors())->flatten()->all(),
                )));
            } catch (Throwable $exception) {
                report($exception);

                $result->failures[] = new RowFailure($line, $row, [__('filament-import::import.errors.unexpected')]);
            }

            $import->processed_rows++;
        }

        $import->successful_rows = $result->succeeded();

        return $result;
    }

    protected function modelConnection(): ?string
    {
        if (is_string($this->importer) && is_string($model = $this->importer::getModel())) {
            return (new $model)->getConnectionName();
        }

        return null;
    }

    /**
     * @param  array<string, string>  $columnMap
     */
    protected function makeImporter(Import $import, array $columnMap): Importer
    {
        if ($this->importer instanceof Closure) {
            return ($this->importer)($import, $columnMap, $this->options);
        }

        return new ($this->importer)($import, $columnMap, $this->options);
    }

    /**
     * Counts data rows, stopping one past the limit: enough to refuse an
     * oversized file without reading all of it.
     */
    protected function countRows(int $limit): int
    {
        $count = 0;

        foreach ($this->reader->rows() as $ignored) {
            if (++$count > $limit) {
                break;
            }
        }

        return $count;
    }
}
