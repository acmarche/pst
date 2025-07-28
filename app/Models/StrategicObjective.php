<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class StrategicObjective extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'position',
        'department',
    ];

    /**
     * Get the operational objectives for the strategic objective.
     * @return HasMany<OperationalObjective>
     */
    public function oos(): HasMany
    {
        return $this->hasMany(OperationalObjective::class);
    }
}
