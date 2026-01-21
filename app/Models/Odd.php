<?php

namespace App\Models;

use Database\Factories\OddFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Scout\Searchable;

/**
 * Objectif de développement durable
 */
#[UseFactory(OddFactory::class)]
final class Odd extends Model
{
    use HasFactory, Searchable;

    protected $fillable = [
        'name',
        'icon',
        'color',
        'description',
        'position',
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
            'description' => $this->description,
        ];
    }

    /**
     * Get the name of the index associated with the model.
     */
    public function searchableAs(): string
    {
        return 'pst_odds_index';
    }

    /**
     * @return BelongsToMany<Action>
     */
    public function actions(): BelongsToMany
    {
        return $this->belongsToMany(Action::class);
    }
}
