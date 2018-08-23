<?php

namespace Matthewnw\Permissions\Models;

use Illuminate\Database\Eloquent\Model;
use Matthewnw\Permissions\Contracts\Permission as PermissionContract;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Matthewnw\Permissions\Exceptions\PermissionDoesNotExist;
use Matthewnw\Permissions\PermissionsRegistrar;

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
        $this->setTable(config('permission.table_names.permissions'));
        parent::__construct($attributes);
    }

    /**
     * A permission may belong to various roles.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permissions.models.role'),
            config('permissions.table_names.role_permissions'),
            'permission_id', 'role_id');
    }

    /**
     * A permission belongs to some users of the model associated with its guard.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permissions.models.user'),
            config('permissions.table_names.user_permissions'),
            'permission_id', 'user_id');
    }

    /**
     * Scope to only retrieve active roles
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query): Builder
    {
        return $query->where('active', '=', 1);
    }

    /**
     * Find a permission by its identity.
     *
     * @param string $identity
     *
     * @throws \Matthewnw\Permissions\Exceptions\PermissionDoesNotExist
     *
     * @return \Matthewnw\Permissions\Contracts\Permission
     */
    public static function findByIdentity(string $identity): PermissionContract
    {
        $permission = static::getPermissions()->filter(function ($permission) use ($identity) {
            return $permission->identity === $identity;
        })->first();

        if (! $permission) {
            throw PermissionDoesNotExist::withIdentity($identity);
        }
        return $permission;
    }

    /**
     * Find a permission by its id .
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
            throw PermissionDoesNotExist::withId($id);
        }
        return $permission;
    }

    /**
     * Get the current cached permissions.
     */
    protected static function getPermissions(): Collection
    {
        return app(PermissionsRegistrar::class)->getPermissions();
    }
}
