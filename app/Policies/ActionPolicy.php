<?php

namespace App\Policies;

use App\Constant\RoleEnum;
use App\Models\Action;
use App\Models\User;

// https://laravel.com/docs/12.x/authorization#creating-policies
final class ActionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Action $action): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->hasOneOfThisRoles([RoleEnum::MANDATAIRE->value])) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Action $action): bool
    {
        return $this->isUserLinkedToAction($user, $action);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Action $action): bool
    {
        return $this->isUserLinkedToAction($user, $action);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Action $action): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Action $action): bool
    {
        return false;
    }

    /**
     * Check if user is linked to the action either directly or through services
     */
    private function isUserLinkedToAction(User $user, Action $action): bool
    {
        if ($user->hasOneOfThisRoles([RoleEnum::MANDATAIRE->value])) {
            return false;
        }
        if ($user->hasOneOfThisRoles([RoleEnum::ADMIN->value])) {
            return true;
        }
        if ($user->hasOneOfThisRoles([RoleEnum::RESPONSIBLE->value])) {
            return $action->leaderServices()
                ->whereHas('users', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->exists();
        }

        // Check if user is directly linked to the action
        return $action->users()->where('user_id', $user->id)->exists();
    }
}
