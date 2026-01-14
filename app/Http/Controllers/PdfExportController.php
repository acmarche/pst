<?php

namespace App\Http\Controllers;

use App\Filament\Exports\PdfExport;
use App\Models\Action;

final class PdfExportController extends Controller
{
    public function download(Action $action)
    {
        return PdfExport::exportAcion($action); // returns BinaryFileResponse
    }
}
