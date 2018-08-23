<?php

namespace Matthewnw\Permissions\Test;

use Matthewnw\Permissions\Models\Role;
use Matthewnw\Permissions\PermissionsRegistrar;
use Matthewnw\Permissions\Exceptions\RoleDoesNotExist;

class UserHasRolesTest extends TestCase
{
    /** @test */
    public function it_can_determine_that_the_user_does_not_have_a_role()
    {
        $this->assertFalse($this->testUser->hasRole('testrole'));
    }

    /** @test */
    public function it_can_assign_and_remove_a_role()
    {
        $this->testUser->assignRole('testrole');
        $this->assertTrue($this->testUser->hasRole('testrole'));

        $this->testUser->removeRole('testrole');
        $this->refreshTestUser();
        $this->assertFalse($this->testUser->hasRole('testrole'));
    }

    /** @test */
    public function it_can_assign_a_role_using_an_object()
    {
        $this->testUser->assignRole($this->testUserRole);
        $this->assertTrue($this->testUser->hasRole($this->testUserRole));
    }

    /** @test */
    public function it_can_assign_a_role_using_an_id()
    {
        $this->testUser->assignRole($this->testUserRole->id);
        $this->assertTrue($this->testUser->hasRole($this->testUserRole));
    }

    /** @test */
    public function it_can_assign_multiple_roles_at_once()
    {
        $this->testUser->assignRole($this->testUserRole->id, 'testrole2');
        $this->assertTrue($this->testUser->hasRole('testrole'));
        $this->assertTrue($this->testUser->hasRole('testrole2'));
    }

    /** @test */
    public function it_can_assign_multiple_roles_using_an_array()
    {
        $this->testUser->assignRole([$this->testUserRole->id, 'testrole2']);
        $this->assertTrue($this->testUser->hasRole('testrole'));
        $this->assertTrue($this->testUser->hasRole('testrole2'));
    }

    /** @test */
    public function it_does_not_remove_already_associated_roles_when_assigning_new_roles()
    {
        $this->testUser->assignRole($this->testUserRole->id);
        $this->testUser->assignRole('testrole2');
        $this->assertTrue($this->testUser->fresh()->hasRole('testrole'));
    }

    /** @test */
    public function it_does_not_throw_an_exception_when_assigning_a_role_that_is_already_assigned()
    {
        $this->testUser->assignRole($this->testUserRole->id);
        $this->testUser->assignRole($this->testUserRole->id);
        $this->assertTrue($this->testUser->fresh()->hasRole('testrole'));
    }

    /** @test */
    public function it_throws_an_exception_when_assigning_a_role_that_does_not_exist()
    {
        $this->expectException(RoleDoesNotExist::class);
        $this->testUser->assignRole('evil-emperor');
    }

    /** @test */
    public function it_ignores_null_roles_when_syncing()
    {
        $this->testUser->assignRole('testrole');
        $this->testUser->syncRoles('testrole2', null);
        $this->assertFalse($this->testUser->hasRole('testrole'));
        $this->assertTrue($this->testUser->hasRole('testrole2'));
    }

    /** @test */
    public function it_can_sync_roles_from_a_string()
    {
        $this->testUser->assignRole('testrole');
        $this->testUser->syncRoles('testrole2');
        $this->assertFalse($this->testUser->hasRole('testrole'));
        $this->assertTrue($this->testUser->hasRole('testrole2'));
    }

    /** @test */
    public function it_can_sync_multiple_roles()
    {
        $this->testUser->syncRoles('testrole', 'testrole2');
        $this->assertTrue($this->testUser->hasRole('testrole'));
        $this->assertTrue($this->testUser->hasRole('testrole2'));
    }
    /** @test */
    public function it_can_sync_multiple_roles_from_an_array()
    {
        $this->testUser->syncRoles(['testrole', 'testrole2']);
        $this->assertTrue($this->testUser->hasRole('testrole'));
        $this->assertTrue($this->testUser->hasRole('testrole2'));
    }
    /** @test */
    public function it_will_remove_all_roles_when_an_empty_array_is_passed_to_sync_roles()
    {
        $this->testUser->assignRole('testrole');
        $this->testUser->assignRole('testrole2');
        $this->testUser->syncRoles([]);
        $this->assertFalse($this->testUser->hasRole('testrole'));
        $this->assertFalse($this->testUser->hasRole('testrole2'));
    }

    /** @test */
    public function it_deletes_pivot_table_entries_when_deleting_models()
    {
        $tableNames = config('permissions.table_names');
        $user = User::create(['name' => 'Test User', 'email' => 'user@test.com', 'password' => '1234']);
        $user->assignRole('testrole');
        $user->assignPermission('articles.edit');

        $this->assertDatabaseHas($tableNames['user_permissions'], ['user_id' => $user->id]);
        $this->assertDatabaseHas($tableNames['user_roles'], ['user_id' => $user->id]);

        $user->delete();

        $this->assertDatabaseMissing($tableNames['user_permissions'], ['user_id' => $user->id]);
        $this->assertDatabaseMissing($tableNames['user_roles'], ['user_id' => $user->id]);
    }
}
