<?php
namespace Matthewnw\Permissions\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

interface Permission
{
    /**
     * A permission can be applied to roles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles(): BelongsToMany;

    /**
     * A user can be assigned permissions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): BelongsToMany;

    /**
     * Scope to only retrieve active permissions
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query): Builder;

    /**
     * Find a permission by its identity.
     *
     * @param string $identity
     *
     * @throws \Matthewnw\Permissions\Exceptions\PermissionDoesNotExist
     *
     * @return Permission
     */
    public static function findByIdentity(string $identity): self;

    /**
     * Find a permission by its id.
     *
     * @param int $id
     *
     * @throws \Matthewnw\Permissions\Exceptions\PermissionDoesNotExist
     *
     * @return Permission
     */
    public static function findById(int $id): self;
}
