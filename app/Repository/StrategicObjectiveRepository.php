<?php

namespace App\Repository;

use App\Models\StrategicObjective;
use Illuminate\Support\Collection;

class StrategicObjectiveRepository
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
    public static function findByDepartmentWithOosAndActions(string $department): Collection
    {
        return StrategicObjective::where('department', $department)
            //->withoutGlobalScope(DepartmentScope::class)
            ->with('oos')
            ->with('oos.actions')
            ->with('oos.actions.leaderServices')
            ->with('oos.actions.partnerServices')
            ->with('oos.actions.mandataries')
            ->with('oos.actions.users')
            ->with('oos.actions.partners')
            ->with('oos.actions.odds')
            // ->with(['oos' => fn ($query) => $query->withoutGlobalScope(DepartmentScope::class)])
            ->get();
    }
}
