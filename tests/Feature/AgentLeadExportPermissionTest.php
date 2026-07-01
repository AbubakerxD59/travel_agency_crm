<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AgentLeadExportPermissionTest extends TestCase
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

    public function test_agent_without_export_permission_cannot_export_leads(): void
    {
        $agent = $this->agentWithPermissions(['leads.access']);

        $this->actingAs($agent)
            ->get(route('agent.leads.export'))
            ->assertForbidden();
    }

    public function test_agent_with_export_permission_can_export_leads(): void
    {
        $agent = $this->agentWithPermissions(['leads.access', 'leads.export']);

        $this->actingAs($agent)
            ->get(route('agent.leads.export'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    /**
     * @param  list<string>  $permissions
     */
    private function agentWithPermissions(array $permissions): User
    {
        $agent = User::factory()->create();
        $agent->assignRole('agent');
        $agent->syncPermissions($permissions);

        return $agent;
    }
}
