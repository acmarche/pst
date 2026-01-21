<?php

declare(strict_types=1);

namespace App\Models;

use App\Ldap\User as UserLdap;
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
use Laravel\Scout\Searchable;

#[UseFactory(UserFactory::class)]
final class User extends Authenticatable implements FilamentUser, HasName
{
    use HasFactory, Impersonate, Notifiable,Searchable;

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

    public static function generateDataFromLdap(UserLdap $userLdap, string $username): array
    {
        $email = $userLdap->getFirstAttribute('mail');

        /*   $department = match (true) {
               str_contains($email, 'cpas.marche') => DepartmentEnum::CPAS->value,
               str_contains($email, 'ac.marche') => DepartmentEnum::VILLE->value,
               default => DepartmentEnum::VILLE->value,
           };*/

        return [
            'first_name' => $userLdap->getFirstAttribute('givenname'),
            'last_name' => $userLdap->getFirstAttribute('sn'),
            'email' => $email,
            // 'departments' => [$department],
            'mobile' => $userLdap->getFirstAttribute('mobile'),
            'phone' => $userLdap->getFirstAttribute('telephoneNumber'),
            'extension' => $userLdap->getFirstAttribute('ipPhone'),
            'uuid' => self::getUuidFromIntranetDb($username),
        ];
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'username' => $this->username,
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return true;
        }

        return false;
    }

    public function fullName(): string
    {
        return $this->last_name.' '.$this->first_name;
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name.' '.$this->last_name;
    }

    public function getFilamentName(): string
    {
        return $this->fullName();
    }

    /**
     * The roles that belong to the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
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

    public function hasOneOfThisRoles(array $rolesToFind): bool
    {
        foreach ($this->roles()->get() as $role) {
            if (in_array($role->name, $rolesToFind)) {
                return true;
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
            'departments' => 'array',
        ];
    }

    private static function getUuidFromIntranetDb(string $username): ?string
    {
        $user = UserIntranet::query()->where('username', $username)->first();

        if ($user) {
            return $user->uuid;
        }

        return null;
    }
}
