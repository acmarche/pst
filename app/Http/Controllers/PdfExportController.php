<?php

namespace App\Http\Controllers;

use App\Filament\Exports\PdfExport;
use App\Models\Action;
use Filament\Actions\Action as FilamentAction;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class PdfExportController extends Controller
{
    public function export(Action $action): RedirectResponse
    {
        $relativePath = PdfExport::exportAction($action);

        $downloadUrl = route('pdf.download', ['path' => $relativePath]);

        $recipient = auth()->user();

        Notification::make()
            ->title('Saved successfully')
            ->info()
            ->sendToDatabase($recipient)->actions([
                FilamentAction::make('download')
                    ->button()
                    ->url($downloadUrl)
                    ->markAsRead(),
            ])
            ->send();

        return redirect()->back()->with('success', 'PDF exported successfully');
    }

    public function download(string $path): StreamedResponse
    {
        return Storage::disk('public')->download($path, basename($path), [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
