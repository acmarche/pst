<?php

namespace App\Repository;

use App\Constant\ActionStateEnum;
use App\Models\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ActionRepository
{
    /**
     *
     */
    public static function findByUser(int $userId): Builder
    {
        return Action::query()->whereHas('users', function ($query) use ($userId) {
            $query->where('users.id', $userId);
        })->orWhereHas('leaderServices', function ($query) use ($userId) {
            $query->whereHas('users', function ($query) use ($userId) {
                $query->where('users.id', $userId);
            });
        });
    }

    /**
     * @param int $actionId
     * @return Collection
     */
    public static function findByActionEmailAgents(int $actionId): Collection
    {
        return Action::where('id', $actionId)
            ->with('users')
            ->first()
            ->users
            ->pluck('email')
            ->unique()
            ->values();
    }

    public static function byStateAndDepartment(ActionStateEnum $state, string $department): Builder
    {
        return Action::query()->where('state', $state->value)->where('department', $department);
    }

    public static function byState(ActionStateEnum $state): Collection
    {
        return Action::ofState($state->value)->get();
    }

    public static function all(): int
    {
        return Action::all()->count();
    }

    public static function byDepartment(string $department): Collection
    {
        return Action::query()->where('department', $department)->get();
    }

}
