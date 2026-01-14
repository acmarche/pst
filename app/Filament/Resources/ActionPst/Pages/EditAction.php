<?php

namespace App\Filament\Resources\ActionPst\Pages;

use App\Filament\Resources\ActionPst\ActionPstResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditAction extends EditRecord
{
    protected static string $resource = ActionPstResource::class;

    /**
     * to remove word "editer"
     */
    public function getTitle(): string
    {
        return $this->getRecord()->name;
    }

    /**
     * Hide relation managers on Edit page - they are shown on View page only.
     */
    protected function getAllRelationManagers(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->icon('tabler-eye'),
        ];
    }
}
