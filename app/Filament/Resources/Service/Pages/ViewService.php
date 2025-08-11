<?php

namespace App\Filament\Resources\Service\Pages;

use App\Filament\Resources\Service\RelationManagers\ActionsRelationManager;
use App\Filament\Resources\ServiceResource;
use App\Models\User;
use Filament\Actions;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;

final class ViewService extends ViewRecord
{
    protected static string $resource = ServiceResource::class;

    public function getTitle(): string
    {
        return $this->record->name ?? 'Empty name';
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            Fieldset::make('users_tab')
                ->label('Agents')
                ->schema([
                    TextEntry::make('users')
                        ->label(false)
                        ->badge()
                        ->formatStateUsing(fn (User $state): string => $state->last_name.' '.$state->first_name),
                ]),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->icon('tabler-edit'),
            Actions\DeleteAction::make()
                ->icon('tabler-trash'),
        ];
    }
    protected function getAllRelationManagers(): array
    {
        $relations = $this->getResource()::getRelations();
        $relations[] = ActionsRelationManager::class;

        return $relations;
    }
}
