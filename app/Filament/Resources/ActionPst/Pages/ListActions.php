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
        $department = UserRepository::departmentSelected();
        $tabs = [
            0 => Tab::make('All')
                ->label('Toutes')
                ->badge(function () use ($department): int {
                    return ActionRepository::findByDepartmentWithOosAndActions(
                        UserRepository::departmentSelected()
                    )->count();
                })
                ->modifyQueryUsing(
                    fn(Builder $query) => ActionRepository::findByDepartmentWithOosAndActions(
                        UserRepository::departmentSelected()
                    )
                ),
        ];
        if (auth()->user()->hasRole(RoleEnum::ADMIN->value)) {
            $tabs[1] = Tab::make('ToValidate')
                ->label('A valider')
                ->badge(function (): int {
                    return ActionRepository::toValidate()->count();
                })
                ->badgeColor('warning')
                ->icon('heroicon-m-exclamation-circle')
                ->modifyQueryUsing(function (): Builder {
                    return ActionRepository::toValidate();
                });
        }
        foreach (ActionStateEnum::cases() as $actionStateEnum) {
            $tabs[] =
                Tab::make($actionStateEnum->value)
                    ->badge(function () use ($department, $actionStateEnum): int {
                        return ActionRepository::byStateAndDepartment($actionStateEnum, $department)->count();
                    })
                    ->label($actionStateEnum->getLabel())
                    ->badgeColor($actionStateEnum->getColor())
                    ->icon($actionStateEnum->getIcon())
                    ->modifyQueryUsing(function (Builder $query) use ($actionStateEnum, $department): Builder {
                        return ActionRepository::byStateAndDepartment($actionStateEnum, $department);
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
}
