<?php

namespace App\Filament\Resources\Odd\Pages;

use App\Filament\Resources\Odd\OddResource;
use App\Filament\Resources\Odd\RelationManagers\ActionsRelationManager;
use Filament\Actions;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

final class ViewOdd extends ViewRecord
{
    protected static string $resource = OddResource::class;

    public function getTitle(): string
    {
        return $this->record->name;
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextEntry::make('descripton')
                    ->label(false)
                    ->columnSpanFull(),
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
