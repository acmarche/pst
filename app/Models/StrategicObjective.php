<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

final class StrategicObjective extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'position',
        'department',
        'is_internal',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
    ];

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
