<?php

namespace App\Models;

use Database\Factories\OddFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Objectif de développement durable
 */
#[UseFactory(OddFactory::class)]
final class Odd extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'icon',
        'color',
        'description',
        'position',
    ];

    /**
     * @return BelongsToMany<Action>
     */
    public function actions(): BelongsToMany
    {
        return $this->belongsToMany(Action::class);
    }
}
