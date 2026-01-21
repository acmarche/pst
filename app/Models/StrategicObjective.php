<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Laravel\Scout\Searchable;

final class StrategicObjective extends Model
{
    use HasFactory, Notifiable, Searchable;

    protected $fillable = [
        'name',
        'position',
        'department',
        'is_internal',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
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
        return 'pst_strategic_objectives_index';
    }

    public function isInternal(): bool
    {
        return $this->is_internal;
    }

    /**
     * Get the operational objectives for the strategic objective.
     *
     * @return HasMany<OperationalObjective>
     */
    public function oos(): HasMany
    {
        return $this->hasMany(OperationalObjective::class);
    }
}
