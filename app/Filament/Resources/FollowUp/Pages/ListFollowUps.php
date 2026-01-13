<?php

namespace App\Filament\Resources\FollowUp\Pages;

use App\Filament\Resources\FollowUp\FollowUpResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListFollowUps extends ListRecords
{
    protected static string $resource = FollowUpResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getAllTableRecordsCount().' suivis';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Ajouter un suivi')
                ->icon('tabler-plus'),
        ];
    }
}
