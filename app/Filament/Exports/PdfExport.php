<?php

namespace App\Filament\Exports;

use App\Models\Action;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;

class PdfExport
{
    public static function exportAcion(Action $action): PdfBuilder
    {
        return Pdf::html(view('pdf.action', [
            'invoiceNumber' => '1234',
            'customerName' => 'Grumpy Cat',
            'action' => $action,
        ]))
            //->withBrowsershot(fn(Browsershot $shot) => $shot->setNodeBinary()->setNpmBinary()->setPuppeteerBinary()->setPuppeteerLaunchOptions([]))
            ->download('action-'.$action->id.'.pdf');
        // ->save('action-'.$action->id.'.pdf');
    }
}
