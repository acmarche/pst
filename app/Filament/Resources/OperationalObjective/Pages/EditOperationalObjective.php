<?php

namespace App\Filament\Resources\OperationalObjective\Pages;

use App\Filament\Resources\OperationalObjective\OperationalObjectiveResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditOperationalObjective extends EditRecord
{
    protected static string $resource = OperationalObjectiveResource::class;

    /**
     * to remove word "editer"
     */
    public function getTitle(): string
    {
        return $this->getRecord()->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->icon('tabler-eye'),
        ];
    }
}
