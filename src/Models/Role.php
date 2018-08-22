<?php

namespace Matthewnw\Permissions\Models;

use Illuminate\Database\Eloquent\Model;
use Matthewnw\Permissions\Traits\HasPermissions;

class Role extends Model
{
    use HasPermissions;

    protected $table = 'roles';

    protected $fillable = [
        'name', 'identity', 'description', 'active', 'level', 'default',
    ];

    protected $casts = [
        'active' => 'bool',
        'level' => 'int',
        'default' => 'boolean',
    ];

    protected $appends = [
        'permissions_list',
    ];

    public static function boot()
    {
        parent::boot();

        // Set the default identity if none set based on the name
        self::creating(function ($model) {
            if (! $model->identity) {
                $model->identity = str_slug($model->name);
            }
        });
    }


    /**
     * check if the role is a default role
     *
     * @return boolean
     */
    public function isDefault()
    {
        return $this->default;
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function permissions()
    {
        return $this->belongsToMany(config('permissions.models.permission'), 'role_permissions', 'role_id', 'permission_id');
    }

    public function users()
    {
        return $this->belongsToMany(config('permissions.models.user'), 'user_roles', 'role_id', 'user_id');
    }

    /**
     * @return mixed
     */
    public function getPermissionsListAttribute()
    {
        return $this->permissions->map(function ($permission) {
            return $permission->name;
        });
    }

    public function setIdentityAttribute($value)
    {
        $this->attributes['identity'] = str_slug($value);
    }
}
