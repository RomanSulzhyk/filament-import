<?php

namespace RomanSulzhyk\FilamentImport\Importing;

final class ImportResult
{
    /** @var list<RowFailure> */
    public array $failures = [];

    public int $created = 0;

    public int $updated = 0;

    public int $skipped = 0;

    public function total(): int
    {
        return $this->created + $this->updated + $this->skipped + $this->failed();
    }

    public function failed(): int
    {
        return count($this->failures);
    }

    public function succeeded(): int
    {
        return $this->created + $this->updated;
    }
}
