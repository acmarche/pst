<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class History extends Model
{
    use HasFactory;

    protected $fillable = ['action_id', 'body', 'property', 'old_value', 'new_value', 'user_add'];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (Auth::check()) {
                $model->user_add = Auth::user()->username;
            }
        });
    }

    /**
     * Get the action that owns the followup
     */
    public function actions(): BelongsTo
    {
        return $this->belongsTo(Action::class);
    }
}
