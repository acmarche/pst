<?php

declare(strict_types=1);

use App\Http\Controllers\LoginLinkController;
use App\Http\Controllers\PdfExportController;
use App\Http\Controllers\SelectDepartmentController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/admin'));
Route::get('/loginfromintranet/{uuid}', LoginLinkController::class);
Route::get('/download-action/{action}', [PdfExportController::class, 'download'])->name('download.action');
Route::get('/admin/select/department/{department}', [SelectDepartmentController::class, 'select'])
    ->name('select.department');
