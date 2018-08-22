<?php
namespace Matthewnw\Permissions\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

interface Role
{
    /**
     * A permission can be applied to roles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions(): BelongsToMany;

    /**
     * A user can be assigned roles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): BelongsToMany;

    /**
     * Scope to only retrieve active roles
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query): Builder;

    /**
     * A role may or may not be the default
     */
    public function isDefault(): bool;

    /**
     * Find a role by its identity.
     *
     * @param string $identity
     *
     * @throws \Matthewnw\Permissions\Exceptions\RoleDoesNotExist
     *
     * @return Permission
     */
    public static function findByIdentity(string $identity): self;

    /**
     * Find a permission by its id.
     *
     * @param int $id
     *
     * @throws \Matthewnw\Permissions\Exceptions\RoleDoesNotExist
     *
     * @return Role
     */
    public static function findById(int $id): self;
}
