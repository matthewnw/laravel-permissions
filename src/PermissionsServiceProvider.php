<?php

namespace Matthewnw\Permissions;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Schema;
use Matthewnw\Permissions\Contracts\Permission as PermissionContract;
use Matthewnw\Permissions\Contracts\Role as RoleContract;
use Exception;
use Matthewnw\Permissions\Exceptions\PermissionsLoaderException;

class PermissionsServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/permissions.php', 'permissions');

        $this->registerBindings();
    }

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

        // Migrations
        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations'),
        ], 'migrations');

        try {
            $permissionLoader->registerPermissions();
        } catch (Exception $e) {
            // Only show a warning if running in the console
            if (! $this->app->runningInConsole()) {
                throw new PermissionsLoaderException($e->getMessage());
            }
        }

        $this->app->singleton(PermissionRegistrar::class, function ($app) use ($permissionLoader) {
            return $permissionLoader;
        });
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
}
