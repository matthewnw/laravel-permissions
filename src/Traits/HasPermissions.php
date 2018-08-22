<?php

namespace Matthewnw\Permissions\Traits;

use App\Repositories\Auth\PermissionRepository;
use Matthewnw\Permissions\PermissionRegistrar;
use Matthewnw\Permission\Contracts\Permission;

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
                return app(PermissionRepository::class)->getStoredPermission($permission);
            })
            ->filter(function ($permission) {
                // return only a collection of Permission instances
                return $permission instanceof Permission;
            })
            ->map->id // higher order message to just return the id from each permission
            ->all();
        $this->permissions()->sync($permissions, false);
        // forget all cached permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

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
     * alias to get the permission class from the service container via the PermissionRegistrar
     *
     * @return void
     */
    public function getPermissionClass()
    {
        if (! isset($this->permissionClass)) {
            $this->permissionClass = app(PermissionRegistrar::class)->getPermissionClass();
        }
        return $this->permissionClass;
    }
}
