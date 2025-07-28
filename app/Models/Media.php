<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

class Media extends Model
{
    /** @use HasFactory<\Database\Factories\MediaFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'action_id',
        'uuid',
        'file_name',
        'mime_type',
        'disk',
        'size',
    ];

    /**
     * @return BelongsTo<Action>
     */
    public function action(): BelongsTo
    {
        return $this->belongsTo(Action::class);
    }
}
