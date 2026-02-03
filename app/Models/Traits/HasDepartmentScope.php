<?php

namespace App\Models\Traits;

use App\Enums\ActionSynergyEnum;
use App\Repository\UserRepository;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

trait HasDepartmentScope
{
    #[Scope]
    public function forSelectedDepartment(Builder $query): void
    {
        $department = UserRepository::departmentSelected();
        $query->where(function (Builder $q) use ($department): void {
            $q->where('department', '=', $department)
                ->orWhere('synergy', ActionSynergyEnum::YES);
        });
    }

    #[Scope]
    public function forDepartment(Builder $query, string $department): void
    {
        $query->where(function (Builder $q) use ($department): void {
            $q->where('department', '=', $department)
                ->orWhere('synergy', ActionSynergyEnum::YES);
        });
    }
}
