<?php

namespace Matthewnw\Permissions\Test;

use Orchestra\Testbench\TestCase as Orchestra;
use Illuminate\Database\Schema\Blueprint;
use Matthewnw\Permissions\PermissionsServiceProvider;
use Matthewnw\Permissions\PermissionsRegistrar;
use Matthewnw\Permissions\Contracts\Permission;
use Matthewnw\Permissions\Contracts\Role;

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

    public function setUp()
    {
        parent::setUp();

        $this->setUpDatabase($this->app);

        $this->testUser = User::first();
        $this->testUserRole = app(Role::class)->find(1);
        $this->testUserPermission = app(Permission::class)->find(1);
        $this->testAdminRole = app(Role::class)->find(3);
        $this->testAdminPermission = app(Permission::class)->find(4);
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
        $app['config']->set('auth.providers.users.model', User::class);

        $app['db']->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
            $table->softDeletes();
        });

        foreach (glob(__DIR__.'/../database/migrations/*.php') as $filename) {
            include_once $filename;
        }

        (new \CreatePermissionsTables())->up();

        User::create(['email' => 'test@user.com']);

        $app[Role::class]->create(['identity' => 'testRole', 'name' => 'Test Role']);
        $app[Role::class]->create(['identity' => 'testRole2', 'name' => 'Test Role 2']);
        $app[Role::class]->create(['identity' => 'testAdminRole', 'name' => 'Test Admin Role']);

        $app[Permission::class]->create(['identity' => 'admin.permission', 'name' => 'Admin Permission']);
        $app[Permission::class]->create(['identity' => 'articles.create', 'name' => 'Create Articles']);
        $app[Permission::class]->create(['identity' => 'articles.edit', 'name' => 'Edit Articles']);
        $app[Permission::class]->create(['identity' => 'articles.*', 'name' => 'Wildcard Articles']);
        $app[Permission::class]->create(['identity' => 'news.edit', 'name' => 'Edit News']);
        $app[Permission::class]->create(['identity' => 'blog.edit', 'name' => 'Edit Blog']);
    }

    /**
     * Reload the permissions.
     */
    protected function reloadPermissions()
    {
        app(PermissionsRegistrar::class)->forgetCachedPermissions();
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
}
