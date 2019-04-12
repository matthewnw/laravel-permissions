<?php

namespace Matthewnw\Permissions\Test;

use Orchestra\Testbench\TestCase as Orchestra;
use Illuminate\Database\Schema\Blueprint;
use Matthewnw\Permissions\PermissionsServiceProvider;
use Matthewnw\Permissions\PermissionsRegistrar;
use Matthewnw\Permissions\Contracts\Permission;
use Matthewnw\Permissions\Contracts\Role;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends Orchestra
{
    /** @var \Matthewnw\Permissions\Test\User */
    protected $testUser;

    /** @var \Matthewnw\Permissions\Models\Role */
    protected $testUserRole;

    /** @var \Matthewnw\Permissions\Models\Role */
    protected $testAdminRole;

    /** @var \Matthewnw\Permissions\Models\Permission */
    protected $testUserPermission;

    /** @var \Matthewnw\Permissions\Models\Permission */
    protected $testAdminPermission;

    public function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations();
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations/');

        $this->setUpDatabase($this->app);

        // Force the registration of the policies as the provider was loaded before the migrations
        $this->reloadPermissions();

        $this->testUser = User::first();
        $this->testAdminRole = app(Role::class)->find(1);
        $this->testAdminPermission = app(Permission::class)->find(1);
        $this->testUserRole = app(Role::class)->find(2);
        $this->testUserPermission = app(Permission::class)->find(2);
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            PermissionsServiceProvider::class,
        ];
    }

    /**
     * Set up the environment.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // Use test User model for users provider
        $app['config']->set('auth.providers.users.model', User::class);
    }

    /**
     * Set up the database.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpDatabase($app)
    {
        // Turn on foreign keys for SQlite
        DB::statement(DB::raw('PRAGMA foreign_keys = ON;'));

        User::create(['name' => 'Test User', 'email' => 'test@user.com', 'password' => 'secret']);

        $app[Role::class]->create(['identity' => 'testadminrole', 'name' => 'Test Admin Role', 'active' => true]);
        $app[Role::class]->create(['identity' => 'testrole', 'name' => 'Test Role', 'active' => true]);
        $app[Role::class]->create(['identity' => 'testrole2', 'name' => 'Test Role 2', 'active' => true]);
        $app[Role::class]->create(['identity' => 'inactiverole', 'name' => 'Inactive Role', 'active' => false]);

        $app[Permission::class]->create(['identity' => 'admin.permission', 'name' => 'Admin Permission', 'active' => true]);
        $app[Permission::class]->create(['identity' => 'articles.create', 'name' => 'Create Articles', 'active' => true]);
        $app[Permission::class]->create(['identity' => 'articles.edit', 'name' => 'Edit Articles', 'active' => true]);
        $app[Permission::class]->create(['identity' => 'articles.*', 'name' => 'Wildcard Articles', 'active' => true]);
        $app[Permission::class]->create(['identity' => 'news.edit', 'name' => 'Edit News', 'active' => true]);
        $app[Permission::class]->create(['identity' => 'blog.edit', 'name' => 'Edit Blog', 'active' => true]);
        $app[Permission::class]->create(['identity' => 'inactive.permission', 'name' => 'Inactive Permission', 'active' => false]);
    }

    /**
     * Reload the permissions.
     */
    protected function reloadPermissions()
    {
        app(PermissionsRegistrar::class)->forgetCachedPermissions();
        app(PermissionsRegistrar::class)->registerPermissions();
    }

    /**
     * Refresh the testUser.
     */
    public function refreshTestUser()
    {
        $this->testUser = $this->testUser->fresh();
    }

    /**
     * Refresh the testUserPermission.
     */
    public function refreshTestUserPermission()
    {
        $this->testUserPermission = $this->testUserPermission->fresh();
    }

    /**
     * Refresh the testUserRole.
     */
    public function refreshTestUserRole()
    {
        $this->testUserRole = $this->testUserRole->fresh();
    }
}
