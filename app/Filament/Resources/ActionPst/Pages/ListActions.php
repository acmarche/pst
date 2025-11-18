<?php

namespace App\Filament\Resources\ActionPst\Pages;

use App\Constant\ActionStateEnum;
use App\Constant\RoleEnum;
use App\Filament\Resources\ActionPstResource;
use App\Models\Scopes\ValidatedScope;
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
        $tabs = [
            0 => Tab::make('All')
                ->label('Toutes')
                ->badge(
                    fn () => $this->getFilteredTableQuery()->clone()->withoutGlobalScope(ValidatedScope::class)->count()
                )
                ->modifyQueryUsing(
                    fn (Builder $query) => $query->withoutGlobalScope(ValidatedScope::class)
                ),
        ];
        if (auth()->user()->hasRole(RoleEnum::ADMIN->value)) {
            $tabs[1] = Tab::make('ToValidate')
                ->label('A valider')
                ->badgeColor('warning')
                ->icon('heroicon-m-exclamation-circle')
                ->badge(
                    fn () => $this->getFilteredTableQuery()->clone()->withoutGlobalScope(ValidatedScope::class)->where(
                        'to_validate',
                        true
                    )->count()
                )
                ->modifyQueryUsing(
                    fn (Builder $query) => $query->withoutGlobalScope(ValidatedScope::class)->where('to_validate', true)
                );
        }
        foreach (ActionStateEnum::cases() as $actionStateEnum) {
            $tabs[] =
                Tab::make($actionStateEnum->value)
                    ->badge(
                        fn () => $this->getFilteredTableQuery()->clone()->where(
                            'state',
                            '=',
                            $actionStateEnum->value
                        )->count()
                    )
                    ->label($actionStateEnum->getLabel())
                    ->badgeColor($actionStateEnum->getColor())
                    ->icon($actionStateEnum->getIcon())
                    ->modifyQueryUsing(fn (Builder $query) => $query->where('state', '=', $actionStateEnum->value));
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
