<?php

namespace App\Repository;

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
            ->forDepartment($department)
            ->with('actions')
            ->with('actions.leaderServices')
            ->with('actions.partnerServices')
            ->with('actions.mandataries')
            ->with('actions.users')
            ->with('actions.partners')
            ->with('actions.odds');
    }
}
