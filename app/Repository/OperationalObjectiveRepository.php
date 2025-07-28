<?php

namespace App\Repository;

use Illuminate\Database\Eloquent\Builder;

class OperationalObjectiveRepository
{

    public static function findByDepartment(Builder $query, string $department): Builder
    {
        return $query->department($department)->orderBy('position');
    }
}
