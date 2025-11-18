<?php

namespace App\Filament\Resources\ActionPst\Pages;

use App\Constant\ActionStateEnum;
use App\Constant\RoleEnum;
use App\Filament\Resources\ActionPstResource;
use App\Repository\ActionRepository;
use App\Repository\UserRepository;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;

final class ListActions extends ListRecords
{
    protected static string $resource = ActionPstResource::class;

    public function getTitle(): string|Htmlable
    {
        $this->debugQuery();

        return $this->getAllTableRecordsCount().' actions';
    }

    /**
     * https://github.com/filamentphp/filament/discussions/10803
     * @return array|Tab[]
     */
    public function getTabs(): array
    {
        $filters = $this->tableFilters ?? [];
        $department = UserRepository::departmentSelected();
        if (isset($filters['department']['value']) && $filters['department']['value'] !== null) {
            $department = $filters['department']['value'];
        }

        $tabs = [
            0 => Tab::make('All')
                ->label('Toutes')
                ->badge(
                    fn() => ActionRepository::findByDepartmentWithOosAndActions($department)->count()
                )
                ->modifyQueryUsing(
                    fn(Builder $query) => ActionRepository::findByDepartmentWithOosAndActions($department)
                ),
        ];
        if (auth()->user()->hasRole(RoleEnum::ADMIN->value)) {
            $tabs[1] = Tab::make('ToValidate')
                ->label('A valider')
                ->badgeColor('warning')
                ->icon('heroicon-m-exclamation-circle')
                ->badge(
                    fn() => ActionRepository::byDepartmentAndToValidateOrNot($department, true)->count()
                )
                ->modifyQueryUsing(
                    fn() => ActionRepository::byDepartmentAndToValidateOrNot($department, true)
                );
        }
        foreach (ActionStateEnum::cases() as $actionStateEnum) {
            $tabs[] =
                Tab::make($actionStateEnum->value)
                    ->badge(function () use ($department, $actionStateEnum): int {
                        return ActionRepository::byStateAndDepartment($actionStateEnum, $department)->count();
                    })
                    ->modifyQueryUsing(function (Builder $query) use ($actionStateEnum, $department): Builder {
                        return ActionRepository::byStateAndDepartment($actionStateEnum, $department);
                    })
                    ->label($actionStateEnum->getLabel())
                    ->badgeColor($actionStateEnum->getColor())
                    ->icon($actionStateEnum->getIcon());
        }

        return $tabs;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Ajouter une action')
                ->icon('tabler-plus'),
            Actions\Action::make('list-sheet')
                ->label('Liste comme Google sheet')
                ->icon('tabler-list')
                ->url(ActionPstResource::getUrl('asGoogleSheet')),
        ];
    }

    private function debugQuery(): void
    {
        // Get the query builder
        $builder = $this->getFilteredTableQuery();
        $sql = $builder->toSql();
        $bindings = $builder->getBindings();

        // Replace bindings in SQL
        $fullQuery = $sql;
        foreach ($bindings as $binding) {
            dump('bind: '.$binding);
            $value = is_numeric($binding) ? $binding : "'".$binding."'";
            $fullQuery = preg_replace('/\?/', $value, $fullQuery, 1);
        }

        dump([
            'sql' => $sql,
            'bindings' => implode(',', $bindings),
            'full_query' => $fullQuery,
        ]);
    }
}
