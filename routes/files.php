<?php

use Cabinet\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;

Route::get(
    config('cabinet.file.route_prefix', '/files') . '/{source}/{id}/thumbnail',
    [FileController::class, 'thumbnail']
)
    ->middleware(config('cabinet.file.middleware', ['web']))
    ->name('cabinet.files.thumbnail');

Route::get(
    config('cabinet.file.route_prefix', '/files') . '/{source}/{id}/original',
    [FileController::class, 'original']
)
    ->middleware(config('cabinet.file.middleware', ['web']))
    ->name('cabinet.files.original');

Route::get(
    config('cabinet.file.route_prefix', '/files') . '/{source}/{id}/preview',
    [FileController::class, 'preview']
)
    ->middleware(config('cabinet.file.middleware', ['web']))
    ->name('cabinet.files.preview');
