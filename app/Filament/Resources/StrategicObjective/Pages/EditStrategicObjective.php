<?php

namespace App\Filament\Resources\StrategicObjective\Pages;

use App\Filament\Resources\StrategicObjectiveResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStrategicObjective extends EditRecord
{
    protected static string $resource = StrategicObjectiveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->icon('tabler-eye'),
        ];
    }

    //force remove when edit
    public function getRelationManagers(): array
    {
        return [];
    }

    /**
     * to remove word "editer"
     */
    public  function getTitle(): string
    {
        return $this->getRecord()->name;
    }

}
