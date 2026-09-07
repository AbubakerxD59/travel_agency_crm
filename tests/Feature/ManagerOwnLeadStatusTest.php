<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Country;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ManagerOwnLeadStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Role::findOrCreate('manager', 'web');
        Role::findOrCreate('agent', 'web');
    }

    public function test_manager_can_update_status_of_lead_assigned_to_them(): void
    {
        [$manager, $company] = $this->managerWithCompany();
        $lead = $this->makeLead($manager, $company);

        $this->actingAs($manager)
            ->patch(route('manager.leads.assign.update', $lead), $this->assignPayload($manager, $company, [
                'status' => Lead::STATUS_CONTACTED,
            ]))
            ->assertRedirect(route('manager.leads.index'));

        $this->assertSame(Lead::STATUS_CONTACTED, $lead->fresh()->status);
    }

    public function test_manager_cannot_change_status_of_agent_lead_via_edit_modal(): void
    {
        [$manager, $company, $agent] = $this->managerWithCompany();
        $lead = $this->makeLead($agent, $company, ['status' => Lead::STATUS_NEW]);

        $this->actingAs($manager)
            ->patch(route('manager.leads.assign.update', $lead), $this->assignPayload($agent, $company, [
                'status' => Lead::STATUS_CONTACTED,
            ]))
            ->assertRedirect(route('manager.leads.index'));

        $this->assertSame(Lead::STATUS_NEW, $lead->fresh()->status);
    }

    public function test_manager_must_provide_reason_when_marking_own_lead_not_converted(): void
    {
        [$manager, $company] = $this->managerWithCompany();
        $lead = $this->makeLead($manager, $company);

        $this->actingAs($manager)
            ->from(route('manager.leads.index'))
            ->patch(route('manager.leads.assign.update', $lead), $this->assignPayload($manager, $company, [
                'status' => Lead::STATUS_NOT_CONVERTED,
            ]))
            ->assertRedirect(route('manager.leads.index'))
            ->assertSessionHasErrors('not_converted_reason');

        $this->assertSame(Lead::STATUS_NEW, $lead->fresh()->status);
    }

    public function test_manager_can_mark_own_lead_not_converted_with_reason(): void
    {
        [$manager, $company] = $this->managerWithCompany();
        $lead = $this->makeLead($manager, $company);

        $this->actingAs($manager)
            ->patch(route('manager.leads.assign.update', $lead), $this->assignPayload($manager, $company, [
                'status' => Lead::STATUS_NOT_CONVERTED,
                'not_converted_reason' => 'Chose another agency',
            ]))
            ->assertRedirect(route('manager.leads.index'));

        $lead->refresh();
        $this->assertSame(Lead::STATUS_NOT_CONVERTED, $lead->status);
        $this->assertSame('Chose another agency', $lead->not_converted_reason);
    }

    /**
     * @return array{0: User, 1: Company, 2: User}
     */
    private function managerWithCompany(): array
    {
        $company = Company::query()->create([
            'name' => 'Test Co',
            'country_id' => Country::query()->create(['name' => 'Pakistan'])->id,
        ]);

        $manager = User::factory()->create(['company_id' => $company->id]);
        $manager->assignRole(User::ROLE_MANAGER);

        $agent = User::factory()->create([
            'company_id' => $company->id,
            'manager_id' => $manager->id,
        ]);
        $agent->assignRole(User::ROLE_AGENT);

        return [$manager, $company, $agent];
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function makeLead(User $assignee, Company $company, array $overrides = []): Lead
    {
        return Lead::query()->create(array_merge([
            'agent_id' => $assignee->id,
            'agent_name' => $assignee->name,
            'customer_name' => 'Customer',
            'phone_number' => '03001234567',
            'email' => 'customer@example.com',
            'company_id' => $company->id,
            'city' => 'Lahore',
            'source' => 'google',
            'status' => Lead::STATUS_NEW,
        ], $overrides));
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function assignPayload(User $assignee, Company $company, array $overrides = []): array
    {
        return array_merge([
            'agent_id' => $assignee->id,
            'customer_name' => 'Customer',
            'phone_number' => '03001234567',
            'email' => 'customer@example.com',
            'company_id' => $company->id,
            'city' => 'Lahore',
            'source' => 'google',
            'notes' => 'Note',
        ], $overrides);
    }
}
