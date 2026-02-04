<?php

namespace App\Policies;

use App\Models\Action;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class RegisterPolicies
{
    use ActionEditPolicyTrait;

    public static function register(): void
    {
        Gate::define('teams-edit', function (User $user, Action $action, string $operation) {

            if ($operation === 'create') {
                return true;
            }

            return self::isUserLinkedToAction($user, $action);

        });
    }
}
