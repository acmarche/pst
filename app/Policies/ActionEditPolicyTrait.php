<?php

namespace App\Policies;

use App\Enums\RoleEnum;

trait actionEditPolicyTrait
{
    public function isUserLinkedToAction($user, $action): bool
    {
        if ($user->hasOneOfThisRoles([RoleEnum::MANDATAIRE->value])) {
            return false;
        }
        if ($user->hasOneOfThisRoles([RoleEnum::ADMIN->value])) {
            return true;
        }

        // Check if user is directly linked to the action
        if ($action->users()->where('user_id', $user->id)->exists()) {
            return true;
        }

        return $action->leaderServices()
            ->whereHas('users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->exists();
    }
}
