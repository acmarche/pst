<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Constant\RoleEnum;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Lab404\Impersonate\Models\Impersonate;

#[UseFactory(UserFactory::class)]
final class User extends Authenticatable implements FilamentUser, HasName
{
    use HasFactory, Notifiable, Impersonate;

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'phone',
        'extension',
        'mobile',
        'username',
        'departments',
        'uuid',
        'mandatory',
        'color_primary',
        'color_secondary',
        'email',
        'password',
        'plainPassword',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->hasRole(RoleEnum::ADMIN->value) || $this->hasRole(RoleEnum::AGENT->value);
        }

        return false;
    }

    public function name(): string
    {
        return $this->last_name.' '.$this->first_name;
    }

    public function getFilamentName(): string
    {
        return $this->name();
    }

    /**
     * The roles that belong to the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->BelongsToMany(Role::class);
    }

    public function hasRole(string $roleToFind): bool
    {
        foreach ($this->roles()->get() as $role) {
            if ($role->name === $roleToFind) {
                return true;
            }
        }

        return false;
    }

    public function hasRoles(array $rolesToFind): bool
    {
        foreach ($rolesToFind as $roleToFind) {
            foreach ($this->roles()->get() as $role) {
                if ($role->name === $roleToFind) {
                    return true;
                }
            }
        }

        return false;
    }

    public function addRole(Role $role): void
    {
        $this->roles()->attach($role);
    }

    /**
     * @return BelongsToMany<Service>
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class);
    }

    /**
     * @return BelongsToMany<Action>
     */
    public function actions(): BelongsToMany
    {
        return $this->belongsToMany(Action::class);
    }

    protected static function boot(): void
    {
        parent::boot();

        self::saving(function ($model) {
            // Unset the field so it doesn't save to the database
            if (isset($model->attributes['plainPassword'])) {
                $model->plainPassword = $model->attributes['plainPassword'];
                unset($model->attributes['plainPassword']);
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'roles' => 'array',
            'departments' => 'array',
        ];
    }
}
