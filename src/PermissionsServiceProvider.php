<?php

namespace Matthewnw\Permissions;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;

class PermissionsServiceProvider extends ServiceProvider
{
    protected $permissions;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(PermissionRegistrar $permissionLoader)
    {
        $this->publishes([
            __DIR__. '/../config/permissions.php' => config_path('permissions.php'),
        ], 'config');

        if (! class_exists('CreatePermissionTables')) {
            $timestamp = date('Y_m_d_His', time());
            $this->publishes([
                __DIR__.'/../database/migrations/create_permission_tables.php.stub' => $this->app->databasePath()."/migrations/{$timestamp}_create_permission_tables.php",
            ], 'migrations');
        }

        // Load the permissions
        $permissionLoader->registerPermissions();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/permissions.php', 'permissions'
        );
    }
}
