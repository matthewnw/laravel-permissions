<?php

namespace Matthewnw\Permissions\Test;

use Illuminate\Contracts\Auth\Access\Gate;
use Matthewnw\Permissions\PermissionsRegistrar;

class GateTest extends TestCase
{
    /** @test */
    public function it_can_determine_if_a_user_does_not_have_a_permission()
    {
        $this->assertFalse($this->testUser->can('edit-posts'));
    }

    /** @test */
    public function it_allows_other_gate_before_callbacks_to_run_if_a_user_does_not_have_a_permission()
    {
        $this->assertFalse($this->testUser->can('edit-posts'));

        app(Gate::class)->before(function () {
            return true;
        });

        $this->assertTrue($this->testUser->can('edit-posts'));
    }

    /** @test */
    public function it_can_determine_if_a_user_has_a_direct_permission()
    {
        $this->testUser->assignPermission('articles.edit');

        $this->assertTrue($this->testUser->can('articles.edit'));

        $this->assertFalse($this->testUser->can('non-existing-permission'));

        $this->assertFalse($this->testUser->can('admin.permission'));
    }

    /** @test */
    public function it_can_determine_if_a_user_has_a_permission_through_roles()
    {
        $this->testUserRole->assignPermission($this->testUserPermission->identity);
        $this->testUser->assignRole($this->testUserRole->identity);

        $this->assertTrue($this->testUser->hasRole($this->testUserRole));
        $this->assertTrue($this->testUser->can($this->testUserPermission->identity));
        $this->assertFalse($this->testUser->can('non-existing-permission'));
        $this->assertFalse($this->testUser->can('admin-permission'));
    }
}
