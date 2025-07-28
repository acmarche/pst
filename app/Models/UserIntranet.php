<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class UserIntranet extends Model
{
    protected $connection = 'intranet';
    protected $table = 'users';

    protected $fillable = [

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
}
