<?php

namespace Matthewnw\Permissions;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Schema;
use Matthewnw\Permissions\Contracts\Permission as PermissionContract;
use Matthewnw\Permissions\Contracts\Role as RoleContract;

class PermissionsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(PermissionsRegistrar $permissionLoader)
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

        $this->registerBindings();

        // Check that the migrations have been run before loading the permissions
        try {
            if (Schema::hasTable(config('permissions.table_names.permissions')) &&
                Schema::hasColumn(config('permissions.table_names.permissions'), 'identity')
            ) {
                // Load the permissions
                $permissionLoader->registerPermissions();
            }
        } catch (\Exception $e) {
            // Could not connect to database
        }
    }

    /**
     * Register class bindings for the service container.
     * This allows us to type hint and inject the interfaces instead of a concrete
     * class for the models if using the project uses custom ones
     *
     * @return void
     */
    protected function registerBindings()
    {
        $config = $this->app->config['permissions.models'];

        $this->app->bind(PermissionContract::class, $config['permission']);
        $this->app->bind(RoleContract::class, $config['role']);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/permissions.php', 'permissions');
    }
}
