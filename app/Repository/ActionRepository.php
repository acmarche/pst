<?php

namespace App\Repository;

use App\Constant\ActionStateEnum;
use App\Models\Action;
use App\Models\Scopes\ValidatedScope;
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

    public static function toValidate(): Builder
    {
        return Action::query()->where('to_validate', true);
    }

    public static function byDepartmentAndToValidateOrNot(string $department, bool $state = false): Builder
    {
        return Action::query()->withoutGlobalScope(ValidatedScope::class)->where('to_validate', '=', $state)->where(
            'department',
            $department
        );
    }

    public static function byState(ActionStateEnum $state): Collection
    {
        return Action::ofState($state->value)->get();
    }

    public static function all(): int
    {
        return Action::all()->count();
    }

    public static function findByDepartmentWithOosAndActions(string $department): Builder
    {
        return Action::query()
            ->withoutGlobalScope(ValidatedScope::class)
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
