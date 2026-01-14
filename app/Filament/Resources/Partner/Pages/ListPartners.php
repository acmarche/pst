<?php

namespace App\Filament\Resources\Partner\Pages;

use App\Filament\Resources\Partner\PartnerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

final class ListPartners extends ListRecords
{
    protected static string $resource = PartnerResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getAllTableRecordsCount().' partenaires';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Ajouter un partenaire')
                ->icon('tabler-plus'),
        ];
    }
}
