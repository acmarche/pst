<?php


namespace App\Providers;

use Filament\Forms\Components\RichEditor;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Table;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Model::shouldBeStrict();
        Model::automaticallyEagerLoadRelationships();
        if (!app()->environment('production')) {
            Mail::alwaysTo('jf@marche.be');
        }

        FilamentView::registerRenderHook(
            PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE,
            fn(): View => view('filament.login_form'),
        );
        $this->configureTable();
        $this->configureRichEditor();
    }

    private function configureTable(): void
    {
        Table::configureUsing(function (Table $table): void {
            $table->striped()
                ->deferLoading();
        });
    }

    private function configureRichEditor(): void
    {
        RichEditor::configureUsing(function (RichEditor $richEditor): void {
            $richEditor->toolbarButtons([
                ['bold', 'italic', 'strike', 'link', 'h2', 'h3'],
                ['alignStart', 'alignCenter', 'alignEnd', 'alignJustify'],
                ['bulletList', 'orderedList', 'blockquote', 'horizontalRule'],
                ['table', 'grid'],
                ['textColor', 'clearFormatting'],
                ['undo', 'redo'],
            ]);
        });
    }
}
