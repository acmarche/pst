<?php

namespace App\Models;

use App\Enums\ActionScopeEnum;
use App\Enums\ActionSynergyEnum;
use App\Enums\DepartmentEnum;
use App\Models\Scopes\DepartmentScope;
use App\Models\Traits\HasDepartmentScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Laravel\Scout\Searchable;

final class OperationalObjective extends Model
{
    use HasDepartmentScope, HasFactory, Notifiable, Searchable;

    protected $fillable = [
        'name',
        'position',
        'strategic_objective_id',
        'department',
        'scope',
        'synergy',
    ];

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'department' => $this->department,
        ];
    }

    /**
     * Get the name of the index associated with the model.
     */
    public function searchableAs(): string
    {
        return 'pst_operational_objectives_index';
    }

    public function isInternal(): bool
    {
        return $this->strategicObjective()->first()->isInternal();
    }

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
     * @return HasMany<Action>
     */
    public function actions(): HasMany
    {
        return $this->hasMany(Action::class);
    }

    /**
     * @return HasMany<Action>
     */
    public function actionsForDepartment(): HasMany
    {
        return $this->actions()->forSelectedDepartment();
    }

    protected static function booted(): void
    {
        self::saving(function (OperationalObjective $model) {
            if ($model->scope === ActionScopeEnum::INTERNAL) {
                $model->department = null;
            }
        });
    }

    /**
     * @return array<string, class-string>
     */
    protected function casts(): array
    {
        return [
            'scope' => ActionScopeEnum::class,
            'department' => DepartmentEnum::class,
            'synergy' => ActionSynergyEnum::class,
        ];
    }
}
