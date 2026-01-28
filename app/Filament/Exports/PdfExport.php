<?php

namespace App\Filament\Exports;

use App\Models\Action;
use Spatie\Browsershot\Browsershot;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;

final class PdfExport
{
    public static function exportAction(Action $action): PdfBuilder
    {
        return Pdf::html(view('pdf.action', [
            'invoiceNumber' => '1234',
            'customerName' => 'Grumpy Cat',
            'action' => $action,
        ]))
            ->withBrowsershot(fn (Browsershot $browsershot) => $browsershot
                ->setNodeModulePath('/var/www/puppeteer/node_modules')
            )
            ->download('action-'.$action->id.'.pdf');
        // ->save('action-'.$action->id.'.pdf');
    }
}
