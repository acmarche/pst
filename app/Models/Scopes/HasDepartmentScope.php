<?php

namespace App\Models\Scopes;

use App\Repository\UserRepository;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

trait HasDepartmentScope
{
    #[Scope]
    public function forSelectedDepartment(Builder $query): void
    {
        $department = UserRepository::departmentSelected();
        $query->where('department', '=', $department);
    }

    #[Scope]
    public function forDepartment(Builder $query, string $department): void
    {
        $query->where('department', '=', $department);
    }
}
