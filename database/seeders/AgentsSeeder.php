<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AgentsSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'agent', 'guard_name' => 'web']);

        $defaultAgentPermissions = ['dashboard.access', 'leads.access', 'folders.access'];
        $agents = User::factory()->count(10)->create();
        $agents->each(fn (User $agent) => $agent->assignRole('agent'));
        $agents->each(fn (User $agent) => $agent->syncPermissions($defaultAgentPermissions));
    }
}
