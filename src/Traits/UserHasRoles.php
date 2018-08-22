<?php

namespace App\Models\Traits;

// use App\Repositories\Auth\RoleRepository;

use Matthewnw\Permissions\PermissionRegistrar;
use Matthewnw\Permissions\Contracts\Role;

/**
 * Trait UserHasRoles.
 */
trait UserHasRoles
{
    use HasPermissions;

    private $roleClass;

    /**
     * A user may belong to various roles.
     */
    public function roles()
    {
        return $this->belongsToMany(
            config('permissions.models.role'),
            config('permissions.table_names.user_roles'),
            'user_id', 'role_id');
    }

    /**
     * A user may have various permissions.
     */
    public function permissions()
    {
        return $this->belongsToMany(
            config('permissions.models.permission'),
            config('permissions.table_names.user_permissions'),
            'user_id', 'permission_id');
    }

    /**
     * Assign the given role to the model.
     *
     * @param array|string|\Matthewnw\Permissions\Models\Role ...$roles
     *
     * @return $this
     */
    public function assignRole(...$roles)
    {
        $roles = collect($roles)
            ->flatten()
            ->map(function ($role) {
                if (empty($role)) {
                    return false;
                }
                // get the stored role instance for each passed variable
                return app(RoleRepository::class)->getStoredRole($role);
            })
            ->filter(function ($role) {
                // return only a collection of Role instances
                return $role instanceof Role;
            })
            ->map->id // higher order message to just return the id from each role
            ->all();
        $this->roles()->sync($roles, false);
        // forget all user cached permissions
        $this->forgetCachedUserPermissions($this);

        return $this;
    }

    /**
     * Remove all current roles and set the given ones.
     *
     * @param array|\Matthewnw\Permissions\Models\Role|string ...$roles
     *
     * @return $this
     */
    public function syncRoles(...$roles)
    {
        $this->roles()->detach();
        return $this->assignRole($roles);
    }

    /**
     * Determine if the model has (one of) the given role(s).
     *
     * @param string|array|\Matthewnw\Permissions\Models\Role|\Illuminate\Support\Collection $roles
     *
     * @return bool
     */
    public function hasRole($roles): bool
    {
        if (is_string($roles)) {
            return $this->roles->contains('identity', $roles);
        }
        if ($roles instanceof Role) {
            return $this->roles->contains('id', $roles->id);
        }
        if (is_array($roles)) {
            foreach ($roles as $role) {
                if ($this->hasRole($role)) {
                    return true;
                }
            }
            return false;
        }
        return $roles->intersect($this->roles)->isNotEmpty();
    }

    /**
     * alias to get the role class from the service container via the PermissionRegistrar
     *
     * @return void
     */
    public function getRoleClass()
    {
        if (! isset($this->roleClass)) {
            $this->roleClass = app(PermissionRegistrar::class)->getRoleClass();
        }
        return $this->roleClass;
    }

    /**
     * Forget the cached permissions for the specific user.
     */
    public function forgetCachedUserPermissions()
    {
        app(PermissionRegistrar::class)->forgetCachedUserPermissions($this);
    }
}
