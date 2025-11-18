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
        return $this->getAllTableRecordsCount().' actions';
    }

    public function getTabs(): array
    {
        $departmentSelected = UserRepository::departmentSelected();

        // Get selected filters
        $filters = $this->tableFilters ?? [];


        /*   ->badge(function () use ($departmentSelected): int {
                    return ActionRepository::findByDepartmentWithOosAndActions(
                        $departmentSelected
                    )->count();
                })*/

         /*  ->badge(function () use ($departmentSelected, $actionStateEnum): int {
                        return ActionRepository::byStateAndDepartment($actionStateEnum, $departmentSelected)->count();
                    })*/
        $tabs = [
            0 => Tab::make('All')
                ->label('Toutes')
                ->badge(function () use ($departmentSelected, $filters): int {
                    $query = ActionRepository::findByDepartmentWithOosAndActions(
                        $departmentSelected
                    );

                    return $this->applyFiltersToQuery($query, $filters)->count();
                })
                ->modifyQueryUsing(
                    fn (Builder $query) => $this->applyFiltersToQuery(
                        ActionRepository::findByDepartmentWithOosAndActions($departmentSelected),
                        $filters
                    )
                ),
        ];
        if (auth()->user()->hasRole(RoleEnum::ADMIN->value)) {
            $tabs[1] = Tab::make('ToValidate')
                ->label('A valider')
                ->badge(function () use ($filters): int {
                    $query = ActionRepository::toValidate();

                    return $this->applyFiltersToQuery($query, $filters)->count();
                })
                ->badgeColor('warning')
                ->icon('heroicon-m-exclamation-circle')
                ->modifyQueryUsing(function () use ($filters): Builder {
                    return $this->applyFiltersToQuery(ActionRepository::toValidate(), $filters);
                });
        }
        foreach (ActionStateEnum::cases() as $actionStateEnum) {
            $tabs[] =
                Tab::make($actionStateEnum->value)
                    ->badge(function () use ($departmentSelected, $actionStateEnum, $filters): int {
                        $query = ActionRepository::byStateAndDepartment($actionStateEnum, $departmentSelected);

                        return $this->applyFiltersToQuery($query, $filters)->count();
                    })
                    ->label($actionStateEnum->getLabel())
                    ->badgeColor($actionStateEnum->getColor())
                    ->icon($actionStateEnum->getIcon())
                    ->modifyQueryUsing(function (Builder $query) use ($actionStateEnum, $departmentSelected, $filters): Builder {
                        return $this->applyFiltersToQuery(
                            ActionRepository::byStateAndDepartment($actionStateEnum, $departmentSelected),
                            $filters
                        );
                    });
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

    private function applyFiltersToQuery(Builder $query, array $filters): Builder
    {
        // Apply operational_objectives filter
        if (isset($filters['operational_objectives']['value']) && $filters['operational_objectives']['value'] !== null) {
            $query->where('operational_objective_id', $filters['operational_objectives']['value']);
        }

        // Apply state filter (skip for state-specific tabs)
        if (isset($filters['state']['value']) && $filters['state']['value'] !== null) {
            $query->where('state', $filters['state']['value']);
        }

        // Apply type filter
        if (isset($filters['type']['value']) && $filters['type']['value'] !== null) {
            $query->where('type', $filters['type']['value']);
        }

        // Apply isInternal filter
        if (isset($filters['isInternal']['value']) && $filters['isInternal']['value'] !== null) {
            $isInternal = $filters['isInternal']['value'] === 'true';
            $query->whereHas('operationalObjective', function (Builder $q) use ($isInternal) {
                $q->whereHas('strategicObjective', function (Builder $q) use ($isInternal) {
                    $q->where('is_internal', $isInternal);
                });
            });
        }

        // Apply department filter
        if (isset($filters['department']['value']) && $filters['department']['value'] !== null) {
            $query->where('department', $filters['department']['value']);
        }

        // Apply users filter
        if (isset($filters['users']['value']) && $filters['users']['value'] !== null) {
            $query->whereHas('users', function (Builder $q) use ($filters) {
                $q->where('users.id', $filters['users']['value']);
            });
        }

        return $query;
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
            $value = is_numeric($binding) ? $binding : "'".$binding."'";
            $fullQuery = preg_replace('/\?/', $value, $fullQuery, 1);
        }
        dump(UserRepository::listDepartmentOfCurrentUser());
        dump([
            'sql' => $sql,
            'bindings' => implode(',', $bindings),
            'full_query' => $fullQuery,
        ]);
    }
}
