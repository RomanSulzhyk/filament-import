<?php

namespace RomanSulzhyk\FilamentImport\Importing;

final class RowFailure
{
    /**
     * @param  int  $row  1-based line number in the uploaded file.
     * @param  array<string, mixed>  $data  The row as it appeared in the file.
     * @param  list<string>  $messages  Human-readable reasons, safe to show the user.
     */
    public function __construct(
        public readonly int $row,
        public readonly array $data,
        public readonly array $messages,
    ) {}
}
