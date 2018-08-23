<?php

namespace Matthewnw\Permissions\Models;

use Illuminate\Database\Eloquent\Model;
use Matthewnw\Permissions\Contracts\Role as RoleContract;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Matthewnw\Permissions\Traits\HasPermissions;
use Matthewnw\Permissions\Exceptions\RoleDoesNotExist;
use Matthewnw\Permissions\Traits\RefreshesPermissionsCache;

class Role extends Model implements RoleContract
{
    use HasPermissions,
        RefreshesPermissionsCache;

    protected $fillable = [
        'name', 'identity', 'description', 'active', 'level', 'default',
    ];

    protected $casts = [
        'active' => 'bool',
        'level' => 'int',
        'default' => 'bool',
    ];

    public function __construct(array $attributes = [])
    {
        $this->setTable(config('permission.table_names.roles'));
        parent::__construct($attributes);
    }

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
     * @return bool
     */
    public function isDefault() : bool
    {
        return $this->default;
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /**
     * A role may have various permissions.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permissions.models.permission'),
            config('permissions.table_names.role_permissions'),
            'role_id',
            'permission_id'
        );
    }

    /**
     * A role may belong to various users.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            config('auth.providers.users.model'),
            config('permissions.table_names.user_roles'),
            'role_id',
            'user_id'
        );
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
     * @throws \Matthewnw\Permissions\Exceptions\RoleDoesNotExist
     *
     * @return \Matthewnw\Permissions\Contracts\Role
     */
    public static function findByIdentity(string $identity): RoleContract
    {
        $role = static::where('identity', $identity)->first();

        if (! $role) {
            throw RoleDoesNotExist::withIdentity($identity);
        }
        return $role;
    }

    /**
     * Find a permission by its id .
     *
     * @param int $id
     * @param string|null $guardName
     *
     * @throws \Matthewnw\Permissions\Exceptions\RoleDoesNotExist
     *
     * @return \Matthewnw\Permissions\Contracts\Role
     */
    public static function findById(int $id): RoleContract
    {
        $role = static::where('id', $id)->first();

        if (! $role) {
            throw RoleDoesNotExist::withId($id);
        }

        return $role;
    }

    /**
     * Mutator to filter and set the identity
     *
     * @param string $value
     * @return void
     */
    public function setIdentityAttribute($value)
    {
        $this->attributes['identity'] = str_slug($value);
    }
}
