<?php

namespace App\Filament\Resources\ActionPst\Pages;

use App\Filament\Resources\ActionPst\Tables\ActionTables;
use App\Filament\Resources\ActionPstResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

final class ListActionsAsGoogleSheet extends ListRecords
{
    protected static string $resource = ActionPstResource::class;

    public function getLayout(): string
    {
        return self::$layout ?? 'filament-panels::components.layout.base';
    }

    public function getTitle(): string|Htmlable
    {
        return $this->getAllTableRecordsCount().' actions';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return new HtmlString(
            '<div class="flex items-center gap-2">'.
            '<svg class="w-5 h-5 fi-icon fi-size-md text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">'.
            '<path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5v15m6-15v15m-10.875 0h15.75c.621 0 1.125-.504 1.125-1.125V5.625c0-.621-.504-1.125-1.125-1.125H4.125C3.504 4.5 3 5.004 3 5.625v13.5c0 .621.504 1.125 1.125 1.125Z" />'.
            '</svg>'.
            '<span>Utilisez le sélecteur de colonnes pour afficher les colonnes que vous souhaitez voir.</span>'.
            '</div>'
        );
    }

    public function table(Table $table): Table
    {
        return ActionTables::full($table);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('return')
                ->label('Retour')
                ->icon('tabler-arrow-left')
                ->url(ActionPstResource::getUrl('index')),
            Actions\CreateAction::make('create')
                ->label('Ajouter une action')
                ->icon('tabler-plus'),
        ];
    }
}
