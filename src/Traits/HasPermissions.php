<?php

namespace Matthewnw\Permissions\Traits;

use App\Repositories\Auth\PermissionRepository;
use Matthewnw\Permissions\PermissionsRegistrar;
use Matthewnw\Permissions\Contracts\Permission;

/**
 * Trait UserHasPermissions.
 */
trait HasPermissions
{
    private $permissionClass;

    /**
     * Assign the given role to the model.
     *
     * @param array|string|\Matthewnw\Permissions\Models\Permission ...$permissions
     *
     * @return $this
     */
    public function assignPermission(...$permissions)
    {
        $permissions = collect($permissions)
            ->flatten()
            ->map(function ($permission) {
                if (empty($permission)) {
                    return false;
                }
                // get the stored permission instance for each passed variable
                return $this->getPermissionClass()::findByIdentity($permission);
            })
            ->filter(function ($permission) {
                // return only a collection of Permission instances
                return $permission instanceof Permission;
            })
            ->map->id // higher order message to just return the id from each permission
            ->all();

        $this->permissions()->sync($permissions, false); // second false argument specifies to leave current relations in-tact

        // forget all cached permissions including user cached permissions
        $this->forgetCachedPermissions();

        return $this;
    }

    /**
     * Remove all current permissions and set the given ones.
     *
     * @param array|\Matthewnw\Permissions\Models\Permission|string ...$permissions
     *
     * @return $this
     */
    public function syncPermissions(...$permissions)
    {
        $this->permissions()->detach();
        return $this->assignPermission($permissions);
    }

    /**
     * alias to get the permission class from the service container via the PermissionsRegistrar
     *
     * @return \Matthewnw\Permissions\Contracts\Permission
     */
    public function getPermissionClass()
    {
        if (! isset($this->permissionClass)) {
            $this->permissionClass = app(PermissionsRegistrar::class)->getPermissionClass();
        }
        return $this->permissionClass;
    }

    /**
     * Alias to Forget all cached permissions.
     */
    public function forgetCachedPermissions()
    {
        app(PermissionsRegistrar::class)->forgetCachedPermissions();
    }
}
