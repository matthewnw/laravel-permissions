<?php

namespace Matthewnw\Permissions;

use Illuminate\Support\Collection;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Auth\Access\Authorizable;

class PermissionsRegistrar
{
    /** @var \Illuminate\Contracts\Auth\Access\Gate */
    protected $gate;

    /** @var \Illuminate\Contracts\Cache\Repository */
    protected $cache;

    /** @var string */
    protected $cacheKey = 'matthewnw.permissions';

    /** @var string */
    protected $permissionClass;

    /** @var string */
    protected $roleClass;

    /** @var string */
    protected $userClass;

    public function __construct(Gate $gate, Repository $cache)
    {
        $this->gate = $gate;
        $this->cache = $cache;
        $this->permissionClass = config('permissions.models.permission');
        $this->roleClass = config('permissions.models.role');
        $this->userClass = config('auth.providers.users.model');
    }

    /**
     * Load all permissions from the database and cache if not already cached
     *
     * @return void
     */
    public function registerPermissions()
    {
        // Load the static permissions from the database
        $this->getPermissions()->each(function ($permission) {
            $identity = $permission->identity;
            // Define the gate for each saved permission
            $this->gate->define($identity, function (Authorizable $user) use ($identity) {
                // Get the user permissions either from the cache or load from the database
                $userPermissions = $this->getUserPermissions($user);

                if ($userPermissions) {
                    // Check if using wildcard permissions
                    if (config('permissions.use_wildcard_permissions')){
                        $altPermissions = $this->getWildcardPermissions($identity);
                        // Check if the identity or variations are in the user permissions
                        return null !== $userPermissions->first(function (string $identity) use ($altPermissions) {
                            return in_array($identity, $altPermissions, true);
                        });
                    }else{
                        // Using strict permission identity checks only
                        return null !== $userPermissions->firstWhere('identity', $identity);
                    }
                }

                return false;
            });
        });
    }

    /**
     * Retrieve the permissions collection from the database or cache if available
     *
     * @return Collection
     */
    public function getPermissions(): Collection
    {
        $permissionClass = $this->getPermissionClass();

        return $this->cache->remember($this->cacheKey, config('permission.cache_expiration_time'), function () use ($permissionClass) {
            return $permissionClass->get();
        });
    }

    /**
     * Retrieve the user specific permissions collection from the database or
     * cache if available
     *
     * @param Authorizable $user
     * @return Collection
     */
    public function getUserPermissions(Authorizable $user): Collection
    {
        return $this->cache->remember(
            $this->getUserCacheKey($user),
            config('permissions.cache_expiration_time'),
            function () use($user) {
                // closure for checking based on user id
                $userClosure = function ($query) use ($user) {
                    $query->where('users.id', '=', $user->id);
                };
                // Get all permissions for a user based on direct relation of through roles
                $userPermissions = $this->getPermissionClass()->query()
                    ->active()
                    ->whereHas('roles', function ($query) use ($userClosure) {
                        $query->active()->whereHas('users', $userClosure);
                    })
                    ->orWhereHas('users', $userClosure)
                    ->groupBy('permissions.id')
                    ->pluck('identity');
                return $userPermissions;
            }
        );
    }

    /**
     * Get alternative wildcard permissions for users such as 'team.*'
     * without specifying the exact permissions
     *
     * @param string $permission passed as the 'identity' sluggable from the model
     * @return array passes back an acceptable array of matches based on the stored permission
     */
    protected function getWildcardPermissions(string $permission): array
    {
        $altPermissions = ['*', $permission];
        $permParts = explode('.', $permission);

        if ($permParts && count($permParts) > 1) {
            $currentPermission = '';
            for ($i = 0; $i < (count($permParts) - 1); $i++) {
                $currentPermission .= $permParts[$i] . '.';
                $altPermissions[] = $currentPermission . '*';
            }
        }

        return $altPermissions;
    }

    /**
     * get the unique cache key for the user permissions
     *
     * @param Authorizable $user
     * @return string
     */
    protected function getUserCacheKey(Authorizable $user): string
    {
        return $this->cacheKey . '.user.' . $user->id;
    }

    /**
     * Forget the user permissions cache
     *
     * @return void
     */
    public function forgetCachedPermissions()
    {
        $this->cache->forget($this->cacheKey);

        // Loop through all user accounts and forget their cached permissions
        $this->getUserClass()::all()->each(function($user) {
            $this->forgetCachedUserPermissions($user);
        });
    }

    /**
     * Forget the user permissions cache
     *
     * @param Authorizable $user
     * @return void
     */
    public function forgetCachedUserPermissions(Authorizable $user)
    {
        $this->cache->forget($this->getUserCacheKey($user));
    }

    /**
     * Get an instance of the Permission class from the service container
     *
     * @return void
     */
    public function getPermissionClass()
    {
        return app($this->permissionClass);
    }

    /**
     * Get an instance of the Role class from the service container
     *
     * @return void
     */
    public function getRoleClass()
    {
        return app($this->roleClass);
    }

    /**
     * Get an instance of the User class from the service container
     *
     * @return void
     */
    public function getUserClass()
    {
        return app($this->userClass);
    }
}
