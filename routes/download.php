<?php

use Cabinet\Facades\Cabinet;
use Illuminate\Support\Facades\Route;

Route::get(config('cabinet.download.route'), function () {
    if (!request()->hasValidSignature()) {
        abort(401, 'Invalid signature');
    }

    $source = request()->query('source');
    $fileId = request()->query('file_id');
    $variant = request()->query('variant') ?? null;

    $file = Cabinet::file($source, $fileId);

    if (!$file) {
        abort(404, 'File not found');
    }

    $url = $file->url($variant);


    $ext = str($file->path())->afterLast('.')->toString();
    $name = str($file->name)
        ->replace('\'', '')
        ->slug()
        ->append(".{$ext}")
        ->toString();

    return response()->streamDownload(function () use ($url) {
        // Stream download the file from the URL
        $handle = fopen($url, 'r');
        fpassthru($handle);
        fclose($handle);
    }, $name);
})
    ->middleware(config('cabinet.download.middleware'))
    ->name('cabinet.download');