<?php

namespace Matthewnw\Permissions;

use Illuminate\Support\Collection;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Auth\Access\Authorizable;

class PermissionRegistrar
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
        $this->userClass = config('permissions.models.user');
    }

    /**
     * Load all permissions from the database and cache if not already cached
     *
     * @return void
     */
    public function registerPermissions()
    {
        // Load the static permissions from the database
        $this->getPermissions()->each(function (string $identity) {
            $this->gate->define($identity, function (Authorizable $user) use ($identity) {
                $userPermissions = $this->cache->remember($this->getUserCacheKey($user), config('permissions.cache_expiration_time'), function () {
                    // closure for checking based on user id
                    $userClosure = function ($query) use ($user) {
                        $query->where('users.id', '=', $user->id);
                    };
                    // Get all permissions for a user based on direct relation of through roles
                    $userPermissions = $this->getPermissionClass->query()
                        ->whereHas('roles', function ($query) use ($userClosure) {
                            $query->where('active', '=', 1)
                                ->whereHas('users', $userClosure);
                        })
                        ->orWhereHas('users', $userClosure)
                        ->groupBy('permissions.id')
                        ->where('active', '=', 1)
                        ->pluck('identity');
                    return $userPermissions;
                });

                // check wildcard permissions
                if ($userPermissions) {
                    $altPermissions = $this->getWildcardPermissions($identity);

                    return null !== $userPermissions->first(function (string $identity) use ($altPermissions) {
                        return in_array($identity, $altPermissions, true);
                    });
                }

                return false;
            });
        });

        return true;
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
        // TODO: test the performance on this function and how often it runs
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
