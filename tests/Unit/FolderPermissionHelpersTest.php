<?php

namespace Tests\Unit;

use App\Models\Folder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class FolderPermissionHelpersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (User::assignableAgentPermissionNames() as $name) {
            Permission::findOrCreate($name, 'web');
        }
    }

    public function test_assignable_agent_permissions_exclude_admin_only_permissions(): void
    {
        $assignable = User::assignableAgentPermissionNames();

        $this->assertNotContains('agents.create', $assignable);
        $this->assertNotContains('agents.manage', $assignable);
        $this->assertNotContains('companies.create', $assignable);
        $this->assertNotContains('companies.manage', $assignable);
        $this->assertContains('folders.edit', $assignable);
        $this->assertContains('folders.edit_locked', $assignable);
    }

    public function test_locked_folder_blocks_agent_without_edit_locked_permission(): void
    {
        $agent = User::factory()->create();
        $agent->givePermissionTo(['folders.edit']);

        $folder = new Folder([
            'agent_id' => $agent->id,
            'lock' => true,
        ]);

        $this->assertTrue(folder_is_locked($folder));
        $this->assertFalse(user_can_edit_folder($agent, $folder));
    }

    public function test_locked_folder_allows_agent_with_edit_locked_permission(): void
    {
        $agent = User::factory()->create();
        $agent->givePermissionTo(['folders.edit', 'folders.edit_locked']);

        $folder = new Folder([
            'agent_id' => $agent->id,
            'lock' => true,
        ]);

        $this->assertTrue(user_can_edit_folder($agent, $folder));
    }

    public function test_unlocked_folder_allows_agent_with_folder_edit_permission(): void
    {
        $agent = User::factory()->create();
        $agent->givePermissionTo(['folders.edit']);

        $folder = new Folder([
            'agent_id' => $agent->id,
            'lock' => false,
        ]);

        $this->assertTrue(user_can_edit_folder($agent, $folder));
    }

    public function test_agent_without_folder_edit_permission_cannot_edit_folder(): void
    {
        $agent = User::factory()->create();
        $agent->givePermissionTo(['folders.access']);

        $folder = new Folder([
            'agent_id' => $agent->id,
            'lock' => false,
        ]);

        $this->assertFalse(user_can_edit_folder($agent, $folder));
        $this->assertFalse(user_can_create_folder($agent));
    }
}
