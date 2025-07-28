<?php

namespace App\Filament\Resources\StrategicObjective\Pages;

use App\Constant\DepartmentEnum;
use App\Filament\Exports\StrategicObjectiveExport;
use App\Filament\Resources\StrategicObjectiveResource;
use App\Models\StrategicObjective;
use App\Repository\StrategicObjectiveRepository;
use App\Repository\UserRepository;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Facades\Excel;

class ListOs extends ListRecords
{
    protected static string $resource = StrategicObjectiveResource::class;

    protected string $view = 'filament.resources.strategic-objective-list';

    /**
     * @var Collection|StrategicObjective[] $oss
     */
    private Collection|array $oss = [];

    public function getTitle(): string|Htmlable
    {
        return $this->getAllTableRecordsCount().' objectifs stratégiques (OS)';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Exporter en Xlsx')
                ->icon('tabler-download')
                ->color('secondary')
                ->action(
                    fn() => Excel::download(
                        new StrategicObjectiveExport(UserRepository::departmentSelected()),
                        'pst.xlsx'
                    )
                ),
            Actions\CreateAction::make()
                ->label('Ajouter un OS')
                ->icon('tabler-plus'),
        ];
    }

    public function mount(): void
    {
        parent::mount();
        $this->oss = StrategicObjectiveRepository::findByDepartmentWithOosAndActions(DepartmentEnum::VILLE->value);
    }
}
