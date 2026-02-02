<?php

namespace App\Models;

use App\Enums\ActionRoadmapEnum;
use App\Enums\ActionScopeEnum;
use App\Enums\ActionStateEnum;
use App\Enums\ActionSynergyEnum;
use App\Enums\ActionTypeEnum;
use App\Enums\RoleEnum;
use App\Observers\ActionObserver;
use App\Repository\UserRepository;
use Database\Factories\ActionFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Scout\Searchable;

#[ObservedBy([ActionObserver::class])]
#[UseFactory(ActionFactory::class)]
final class Action extends Model
{
    use HasFactory, Notifiable;
    use Searchable;

    protected $fillable = [
        'name',
        'state',
        'state_percentage',
        'type',
        'roadmap',
        'note',
        'department',
        'due_date',
        'description',
        'evaluation_indicator',
        'work_plan',
        'budget_estimate',
        'financing_mode',
        'operational_objective_id',
        'user_add',
        'synergy',
        'position',
        'to_validate',
        'scope',
    ];

    protected $casts = [
        'medias' => 'array',
        'due_date' => 'datetime',
        'state' => ActionStateEnum::class,
        'type' => ActionTypeEnum::class,
        'synergy' => ActionSynergyEnum::class,
        'roadmap' => ActionRoadmapEnum::class,
        'to_validate' => 'boolean',
        'scope' => ActionScopeEnum::class,
    ];

    #[Scope]
    public static function byState(Builder $query, string $state): void
    {
        $query->where('state', $state);
    }

    #[Scope]
    public static function validated(Builder $query): void
    {
        $query->where('to_validate', false);
    }

    #[Scope]
    public static function toBeValidated(Builder $query): void
    {
        $query->where('to_validate', true);
    }

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
            'description' => $this->description,
            'department' => $this->department,
            'note' => $this->note,
        ];
    }

    /**
     * Get the name of the index associated with the model.
     */
    public function searchableAs(): string
    {
        return 'pst_actions_index';
    }

    public function isInternal(): bool
    {
        return $this->scope === ActionScopeEnum::INTERNAL;
    }

    /**
     * Get the operational objective that owns the action.
     */
    public function operationalObjective(): BelongsTo
    {
        return $this->belongsTo(OperationalObjective::class);
    }

    public function linkedActions(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'action_related',
            'action_id',
            'related_action_id'
        );
    }

    /**
     * @return BelongsToMany<Service>
     */
    public function leaderServices(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'action_service_leader');
    }

    /**
     * @return BelongsToMany<Service>
     */
    public function partnerServices(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'action_service_partner');
    }

    /**
     * Agents pilotes
     *
     * @return BelongsToMany<User>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function mandataries(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'action_mandatory')
            ->whereHas('roles', function ($query) {
                $query->where('name', RoleEnum::MANDATAIRE->value);
            });
    }

    /**
     * @return BelongsToMany<Partner>
     */
    public function partners(): BelongsToMany
    {
        return $this->belongsToMany(Partner::class);
    }

    /**
     * @return BelongsToMany<Odd>
     */
    public function odds(): BelongsToMany
    {
        return $this->belongsToMany(Odd::class);
    }

    /**
     * @return HasMany<Media>
     */
    public function medias(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    /**
     * Get the followups for the action.
     *
     * @return HasMany<FollowUp>
     */
    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class);
    }

    /**
     * Get the followups for the action.
     *
     * @return HasMany<History>
     */
    public function histories(): HasMany
    {
        return $this->hasMany(History::class);
    }

    #[Scope]
    public function departmentSelected(Builder $query): void
    {
        $query->where('department', UserRepository::departmentSelected());
    }

    protected static function booted(): void
    {
        self::creating(function (self $model) {
            if (Auth::check()) {
                $user = Auth::user();
                $model->user_add = $user->username;
            }
            if (! isset($model->to_validate)) {
                $model->to_validate = true;
            }
        });
    }
}
