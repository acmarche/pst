<?php

namespace App\Repository;

use App\Enums\ActionStateEnum;
use App\Models\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class ActionRepository
{
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

    public static function notValidated(): Builder
    {
        return Action::query()->where('validated', false);
    }

    public static function byDepartmentAndValidated(string $department, bool $validated = true): Builder
    {
        return Action::query()
            ->where('validated', '=', $validated)
            ->where('department', $department);
    }

    public static function byState(ActionStateEnum $state): Collection
    {
        return Action::ofState($state->value)->get();
    }

    public static function countAll(): int
    {
        return Action::all()->count();
    }

    public static function findByDepartmentWithOosAndActions(string $department): Builder
    {
        return Action::query()
            ->where('department', $department)
            ->with('operationalObjective')
            ->with('leaderServices')
            ->with('partnerServices')
            ->with('mandataries')
            ->with('users')
            ->with('partners')
            ->with('odds');
    }

    public static function byDepartment(string $department): Collection
    {
        return Action::query()->where('department', $department)->get();
    }

    public static function byDepartmentBuilder(string $department): Builder
    {
        return Action::query()->where('department', $department);
    }
}
