<?php

namespace App\Models;

use App\Models\Scopes\DepartmentScope;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class OperationalObjective extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'position',
        'strategic_objective_id',
        'department',
    ];

    /**
     * Get the strategic objective that owns the operational objective.
     */
    public function strategicObjective(): BelongsTo
    {
        return $this->belongsTo(StrategicObjective::class);
    }

    /**
     * not use
     */
    public function strategicObjectiveWithoutScope(): ?StrategicObjective
    {
        return $this->strategicObjective()
            ->withoutGlobalScope(DepartmentScope::class)
            ->first();
    }

    /**
     * Get the actions for the Operational Objective.
     * @return HasMany<Action>
     */
    public function actions(): HasMany
    {
        return $this->hasMany(Action::class);
    }

    #[Scope]
    public function byDepartment(Builder $builder, string $department): void
    {
        $builder->where('department', $department);
    }

}
