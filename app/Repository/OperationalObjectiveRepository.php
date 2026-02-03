<?php

namespace App\Repository;

use App\Enums\ActionSynergyEnum;
use App\Models\OperationalObjective;
use Illuminate\Database\Eloquent\Builder;

final class OperationalObjectiveRepository
{
    public static function findByDepartment(Builder $query, string $department): Builder
    {
        return $query->department($department)->orderBy('position');
    }

    public static function findByDepartmentWithOosAndActions(string $department): Builder
    {
        return OperationalObjective::query()
            ->where(function (Builder $query) use ($department): void {
                $query->where('department', '=', $department)
                    ->orWhere('synergy', ActionSynergyEnum::YES);
            })
            ->with('actions')
            ->with('actions.leaderServices')
            ->with('actions.partnerServices')
            ->with('actions.mandataries')
            ->with('actions.users')
            ->with('actions.partners')
            ->with('actions.odds');
    }
}
