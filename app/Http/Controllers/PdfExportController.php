<?php

namespace App\Http\Controllers;

use App\Filament\Exports\PdfExport;
use App\Models\Action;
use App\Notifications\PdfExportReadyNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

final class PdfExportController extends Controller
{
    public function export(Action $action): RedirectResponse
    {
        $relativePath = PdfExport::exportAction($action);

        $downloadUrl = Storage::disk('public')->url($relativePath);

        Auth::user()->notify(new PdfExportReadyNotification($action, $downloadUrl));

        return redirect()->back()->with('success', 'PDF exported successfully');
    }
}
