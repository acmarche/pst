<?php

namespace App\Filament\Resources\StrategicObjective\Pages;

use App\Filament\Resources\StrategicObjectiveResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListStrategicObjectives extends ListRecords
{
    protected static string $resource = StrategicObjectiveResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getAllTableRecordsCount().' objectifs stratégiques (OS)';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Ajouter un OS')
                ->icon('tabler-plus'),
        ];
    }
}
