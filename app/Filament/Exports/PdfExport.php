<?php

namespace App\Filament\Exports;

use App\Models\Action;
use Illuminate\Support\Facades\Log;
use Spatie\Browsershot\Browsershot;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;

final class PdfExport
{
    public static function exportAction(Action $action): PdfBuilder
    {
        if ($path = config('pdf.node_modules_path')) {
            Log::error('JFS node_modules_path'.$path);
        }
        if ($path = config('pdf.chrome_path')) {
            Log::error('JFS chrome_path'.$path);
        }

        return Pdf::html(view('pdf.action', [
            'action' => $action,
        ]))

            ->withBrowsershot(function (Browsershot $browsershot): void {
                if ($path = config('pdf.node_modules_path')) {
                    $browsershot->setNodeModulePath($path);
                }
                if ($path = config('pdf.chrome_path')) {
                    $browsershot->setChromePath($path);
                }
            })
            ->download('action-'.$action->id.'.pdf');
        // ->save('action-'.$action->id.'.pdf');
    }
}
