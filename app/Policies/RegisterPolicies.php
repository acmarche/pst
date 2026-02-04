<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\Action;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class RegisterPolicies
{
    public static function register(): void
    {
        Gate::define('teams-edit', function (User $user, Action $action, string $operation) {

            if ($operation === 'create') {
                return true;
            }

            if ($user->hasOneOfThisRoles([RoleEnum::ADMIN->value])) {
                return true;
            }

            return $action->leaderServices()
                ->whereHas('users', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->exists();
        });
    }
}
