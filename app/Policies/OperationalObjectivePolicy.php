<?php

namespace App\Policies;

use App\Constant\RoleEnum;
use App\Models\OperationalObjective;
use App\Models\User;

class OperationalObjectivePolicy
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
    public function view(User $user, OperationalObjective $operationalObjective): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->hasRoles($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, OperationalObjective $operationalObjective): bool
    {
        return $this->hasRoles($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, OperationalObjective $operationalObjective): bool
    {
        return $this->hasRoles($user);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, OperationalObjective $operationalObjective): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, OperationalObjective $operationalObjective): bool
    {
        return false;
    }

    private function hasRoles(User $user): bool
    {
        return $user->hasRoles([RoleEnum::ADMIN->value, RoleEnum::MANAGER->value]);
    }
}
