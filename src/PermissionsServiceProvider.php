<?php

namespace Matthewnw\Permissions;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Schema;

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
        // config
        $this->publishes([
            __DIR__. '/../config/permissions.php' => config_path('permissions.php'),
        ], 'config');

        // migrations
        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations'),
        ], 'migrations');
        // Auto load the migrations if not published
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations/');

        // Check that the migrations have been run
        if (Schema::hasTable(config('permissions.table_names.permissions')) && Schema::hasColumn(config('permissions.table_names.permissions'), 'identity')) {
            // Load the permissions
            $permissionLoader->registerPermissions();
        }
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
