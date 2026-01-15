<?php

namespace App\Filament\Resources\Odd\Pages;

use App\Filament\Resources\Odd\OddResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListOdds extends ListRecords
{
    protected static string $resource = OddResource::class;

    public function getModelLabel(): string
    {
        return 'Objectif de développement durable (ODD)';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Ajouter un ODD')
                ->icon('tabler-plus'),
        ];
    }
}
