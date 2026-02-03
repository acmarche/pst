<?php

declare(strict_types=1);

use App\Http\Controllers\LoginLinkController;
use App\Http\Controllers\PdfExportController;
use App\Http\Controllers\SelectDepartmentController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/admin'));
Route::get('/loginfromintranet/{uuid}', LoginLinkController::class);
Route::get('/export-action/{action}', [PdfExportController::class, 'export'])->name('export.action');
Route::get('/pdf-download/{path}', [PdfExportController::class, 'download'])->name('pdf.download')->where('path', '.*');
Route::get('/admin/select/department/{department}', [SelectDepartmentController::class, 'select'])
    ->name('select.department');
