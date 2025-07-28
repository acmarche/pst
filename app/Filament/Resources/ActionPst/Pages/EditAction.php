<?php

namespace App\Filament\Resources\ActionPst\Pages;

use App\Filament\Resources\ActionPstResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAction extends EditRecord
{
    protected static string $resource = ActionPstResource::class;

    /**
     * to remove word "editer"
     */
    public  function getTitle(): string
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
