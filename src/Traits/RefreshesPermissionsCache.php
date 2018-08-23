<?php

namespace Matthewnw\Permissions\Traits;

use Matthewnw\Permissions\PermissionsRegistrar;

trait RefreshesPermissionsCache
{
    public static function bootRefreshesPermissionsCache()
    {
        static::saved(function () {
            app(PermissionsRegistrar::class)->forgetCachedPermissions();
        });

        static::deleted(function () {
            app(PermissionsRegistrar::class)->forgetCachedPermissions();
        });
    }
}
