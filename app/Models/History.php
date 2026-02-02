<?php

namespace App\Models;

use Database\Factories\HistoryFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

#[UseFactory(HistoryFactory::class)]
final class History extends Model
{
    use HasFactory;

    protected $fillable = ['action_id', 'body', 'property', 'old_value', 'new_value', 'user_add'];

    /**
     * Get the action that owns the followup
     */
    public function actions(): BelongsTo
    {
        return $this->belongsTo(Action::class);
    }

    protected static function booted(): void
    {
        self::creating(function (self $model) {
            if (Auth::check()) {
                $model->user_add = Auth::user()->username;
            }
        });
    }
}
