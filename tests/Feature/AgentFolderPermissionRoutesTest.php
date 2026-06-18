<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Country;
use App\Models\Destination;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AgentFolderPermissionRoutesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Role::findOrCreate('agent', 'web');

        foreach (User::assignableAgentPermissionNames() as $name) {
            Permission::findOrCreate($name, 'web');
        }
    }

    public function test_agent_without_folder_edit_permission_cannot_open_edit_page(): void
    {
        [$agent, $folder] = $this->agentWithFolder(['folders.access']);

        $this->actingAs($agent)
            ->get(route('agent.folders.edit', $folder))
            ->assertForbidden();
    }

    public function test_agent_cannot_edit_locked_folder_without_edit_locked_permission(): void
    {
        [$agent, $folder] = $this->agentWithFolder(['folders.access', 'folders.edit'], locked: true);

        $this->actingAs($agent)
            ->get(route('agent.folders.edit', $folder))
            ->assertForbidden();
    }

    public function test_agent_with_edit_locked_permission_can_edit_locked_folder(): void
    {
        [$agent, $folder] = $this->agentWithFolder(['folders.access', 'folders.edit', 'folders.edit_locked'], locked: true);

        $this->actingAs($agent)
            ->get(route('agent.folders.edit', $folder))
            ->assertOk();
    }

    /**
     * @param  list<string>  $permissions
     * @return array{0: User, 1: Folder}
     */
    private function agentWithFolder(array $permissions, bool $locked = false): array
    {
        $agent = User::factory()->create();
        $agent->assignRole('agent');
        $agent->syncPermissions($permissions);

        $company = Company::query()->create([
            'name' => 'Test Co',
            'country_id' => Country::query()->create(['name' => 'United Kingdom'])->id,
        ]);
        $destination = Destination::query()->create(['name' => 'Jeddah']);

        $folder = Folder::query()->create([
            'agent_id' => $agent->id,
            'agent_name' => $agent->name,
            'order_type' => 'Umrah',
            'vendor_reference' => 'INV-100',
            'customer_name' => 'Test Customer',
            'company_id' => $company->id,
            'destination_id' => $destination->id,
            'travel_date' => now()->toDateString(),
            'booking_date' => now()->toDateString(),
            'lock' => $locked ? 1 : 0,
        ]);

        return [$agent, $folder];
    }
}
