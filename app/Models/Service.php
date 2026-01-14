<?php

namespace App\Models;

use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Notifications\Notifiable;

#[UseFactory(ServiceFactory::class)]
final class Service extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'initials',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function leadingActions(): BelongsToMany
    {
        return $this->belongsToMany(Action::class, 'action_service_leader');
    }

    public function partneringActions(): BelongsToMany
    {
        return $this->belongsToMany(Action::class, 'action_service_partner');
    }
}
