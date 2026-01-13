<?php

namespace App\Policies;

use App\Constant\RoleEnum;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class RegisterPolicies
{
    public static function register(): void
    {
        Gate::define('teams-edit', function (User $user, string $operation) {

            if ($user->hasOneOfThisRoles([RoleEnum::ADMIN->value, RoleEnum::RESPONSIBLE->value])) {
                return true;
            }

            if ($operation === 'create') {
                return true;
            }

            return false;
        });
    }
}
