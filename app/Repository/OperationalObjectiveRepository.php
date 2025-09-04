<?php

namespace App\Repository;

use App\Constant\DepartmentEnum;
use App\Models\OperationalObjective;
use Illuminate\Database\Eloquent\Builder;

class OperationalObjectiveRepository
{
    public static function findByDepartment(Builder $query, string $department): Builder
    {
        return $query->department($department)->orderBy('position');
    }

    public static function findByDepartmentWithOosAndActions(string $department): Builder
    {
        return OperationalObjective::query()->whereIn('department', [$department, DepartmentEnum::COMMON->value])
            // ->withoutGlobalScope(DepartmentScope::class)
            ->with('actions')
            ->with('actions.leaderServices')
            ->with('actions.partnerServices')
            ->with('actions.mandataries')
            ->with('actions.users')
            ->with('actions.partners')
            ->with('actions.odds');
    }
}
