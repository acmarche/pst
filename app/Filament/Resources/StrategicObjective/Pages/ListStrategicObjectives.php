<?php

namespace App\Filament\Resources\StrategicObjective\Pages;

use App\Filament\Exports\StrategicObjectiveExport;
use App\Filament\Resources\StrategicObjective\StrategicObjectiveResource;
use App\Repository\UserRepository;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Maatwebsite\Excel\Facades\Excel;

class ListStrategicObjectives extends ListRecords
{
    protected static string $resource = StrategicObjectiveResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getAllTableRecordsCount().' objectifs stratégiques (OS)';
    }

    protected string $view = 'filament.resources.strategic-objective-list';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Exporter en Xlsx')
                ->icon('tabler-download')
                ->color('secondary')
                ->action(
                    fn () => Excel::download(
                        new StrategicObjectiveExport(UserRepository::departmentSelected()),
                        'pst.xlsx'
                    )
                ),
            Actions\CreateAction::make()
                ->label('Ajouter un OS')
                ->icon('tabler-plus'),
        ];
    }
}
