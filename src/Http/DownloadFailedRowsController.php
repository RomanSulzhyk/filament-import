<?php

namespace RomanSulzhyk\FilamentImport\Http;

use RomanSulzhyk\FilamentImport\Importing\FailedRowsWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadFailedRowsController
{
    public function __invoke(Request $request, string $file): StreamedResponse
    {
        // The signature covers the file, the guard and the user id, so none of
        // them can be swapped. The guard is the one the panel authenticated
        // with: resolving the user through the app's default guard would
        // refuse the admin on a panel with its own guard, and could serve the
        // file to a different person who has the same id in another table.
        abort_unless($request->hasValidSignature(), 403);

        $guard = (string) $request->query('guard');
        $userId = (string) $request->query('user');

        abort_if($userId === '' || $guard === '', 403);
        abort_unless(array_key_exists($guard, (array) config('auth.guards')), 403);
        abort_unless((string) auth()->guard($guard)->id() === $userId, 403);
        abort_unless(preg_match('/^[0-9a-f-]{36}\.xlsx$/', $file) === 1, 404);

        $disk = Storage::disk(FailedRowsWriter::disk());
        $path = FailedRowsWriter::directory() . "/{$file}";

        abort_unless($disk->exists($path), 404);

        return $disk->download($path, 'failed-rows.xlsx');
    }
}
