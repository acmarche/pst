<?php

namespace App\Filament\Widgets;

use App\Constant\ActionStateEnum;
use App\Models\Action;
use App\Repository\UserRepository;
use DB;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ActionsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $department = UserRepository::departmentSelected();
        $actions = Action::select('state', DB::raw('count(*) as total'))
            ->where('actions.department', '=', $department)
            ->groupBy('state')
            ->pluck('total', 'state');

        $stats = [];

        foreach ($actions as $stateKey => $count) {
            $state = ActionStateEnum::from($stateKey);
            $stats[] = Stat::make($state->getLabel(), $count ?? 0)
                ->icon($state->getIcon())
                ->color($state->getColor());
        }

        return $stats;
    }
}
