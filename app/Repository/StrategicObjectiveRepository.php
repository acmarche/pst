<?php

namespace App\Repository;

use App\Enums\ActionSynergyEnum;
use App\Models\StrategicObjective;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class StrategicObjectiveRepository
{
    /**
     * @return Collection|StrategicObjective[]
     */
    public static function findAllWithOosAndActions(): Collection
    {
        return StrategicObjective::with('oos.actions')
            ->orderBy('position')
            ->get();
    }

    /**
     * @return Collection|StrategicObjective[]
     */
    public static function findByDepartmentWithOosAndActions(string $department): Builder
    {
        return StrategicObjective::query()
            ->where(function (Builder $query) use ($department): void {
                $query->where('department', $department)
                    ->orWhereNull('department')
                    ->orWhere('synergy', ActionSynergyEnum::YES);
            })
            ->with('oos')
            ->with('oos.actions')
            ->with('oos.actions.leaderServices')
            ->with('oos.actions.partnerServices')
            ->with('oos.actions.mandataries')
            ->with('oos.actions.users')
            ->with('oos.actions.partners')
            ->with('oos.actions.odds');
    }
}
