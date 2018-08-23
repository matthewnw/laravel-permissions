<?php

namespace Matthewnw\Permissions\Test;

use Illuminate\Auth\Authenticatable;
use Matthewnw\Permissions\Traits\UserHasRoles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class User extends Model implements AuthorizableContract, AuthenticatableContract
{
    use UserHasRoles,
        Authorizable,
        Authenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'password'];

    public $timestamps = false;

    protected $table = 'users';
}
