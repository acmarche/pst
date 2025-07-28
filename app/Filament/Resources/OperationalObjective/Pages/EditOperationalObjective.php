<?php

namespace App\Filament\Resources\OperationalObjective\Pages;

use App\Filament\Resources\OperationalObjectiveResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOperationalObjective extends EditRecord
{
    protected static string $resource = OperationalObjectiveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->icon('tabler-eye'),
        ];
    }

    /**
     * to remove word "editer"
     */
    public  function getTitle(): string
    {
        return $this->getRecord()->name;
    }
}
