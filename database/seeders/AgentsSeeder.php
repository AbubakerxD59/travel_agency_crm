<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AgentsSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'agent', 'guard_name' => 'web']);

        $companies = Company::query()->get();
        if ($companies->isEmpty()) {
            $this->call(CompaniesSeeder::class);
            $companies = Company::query()->get();
        }

        $defaultAgentPermissions = User::defaultAgentPermissions();
        $agents = User::factory()->count(10)->create();

        $agents->each(function (User $agent) use ($companies, $defaultAgentPermissions): void {
            if ($companies->isNotEmpty()) {
                $agent->update(['company_id' => $companies->random()->id]);
            }

            $agent->assignRole(User::ROLE_AGENT);
            $agent->syncPermissions($defaultAgentPermissions);
        });
    }
}
