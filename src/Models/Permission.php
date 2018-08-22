<?php

namespace Matthewnw\Permissions\Models;

use Illuminate\Database\Eloquent\Model;
use Matthewnw\Permissions\Contracts\Permission as PermissionContract;

class Permission extends Model implements PermissionContract
{
    protected $fillable = [
        'name', 'identity', 'group', 'description', 'active',
    ];

    protected $casts = [
        'active' => 'bool',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('permission.table_names.permissions'));
    }

    public function roles()
    {
        return $this->belongsToMany(
            config('permissions.models.role'),
            config('permissions.table_names.role_permissions'),
            'permission_id', 'role_id');
    }

    /**
     * A permission belongs to some users of the model associated with its guard.
     */
    public function users()
    {
        return $this->belongsToMany(
            config('permissions.models.user'),
            config('permissions.table_names.user_permissions'),
            'permission_id', 'user_id');
    }

    /**
     * Find a permission by its name (and optionally guardName).
     *
     * @param string $name
     * @param string|null $guardName
     *
     * @throws \Matthewnw\Permissions\Exceptions\PermissionDoesNotExist
     *
     * @return \Matthewnw\Permissions\Contracts\Permission
     */
    public static function findByName(string $name): PermissionContract
    {
        $permission = static::getPermissions()->filter(function ($permission) use ($name) {
            return $permission->name === $name;
        })->first();
        if (! $permission) {
            throw PermissionDoesNotExist::create($name, $guardName);
        }
        return $permission;
    }

    /**
     * Find a permission by its id (and optionally guardName).
     *
     * @param int $id
     * @param string|null $guardName
     *
     * @throws \Matthewnw\Permissions\Exceptions\PermissionDoesNotExist
     *
     * @return \Matthewnw\Permissions\Contracts\Permission
     */
    public static function findById(int $id): PermissionContract
    {
        $permission = static::getPermissions()->filter(function ($permission) use ($id) {
            return $permission->id === $id;
        })->first();
        if (! $permission) {
            throw PermissionDoesNotExist::withId($id, $guardName);
        }
        return $permission;
    }

    /**
     * Get the current cached permissions.
     */
    protected static function getPermissions(): Collection
    {
        return app(PermissionRegistrar::class)->getPermissions();
    }
}
