<?php

namespace App\Repository;

use App\Constant\DepartmentEnum;

class UserRepository
{
    public static string $department_selected_key = 'department_selected';

    public static function departmentSelected(): string
    {
        $department = session(self::$department_selected_key, null);
        if (!$department) {
            if (auth()->user()) {
                if (count(auth()->user()->departments) > 0) {
                    return auth()->user()->departments[0];
                }
            }
        }

        return DepartmentEnum::VILLE->value;
    }
}
