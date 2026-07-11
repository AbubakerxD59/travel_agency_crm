<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public const SUPER_ADMIN_EMAIL = 'concierge@admin.com';

    /**
     * Strong 8-character password (mixed case, digit, symbol) for the seeded super admin.
     */
    public const SUPER_ADMIN_PASSWORD = 'V7#nQr2!';

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'dashboard.access',
            'agents.create',
            'agents.manage',
            'leads.access',
            'leads.create',
            'leads.export',
            'folders.access',
            'folders.edit',
            'folders.edit_locked',
            'companies.create',
            'companies.manage',
            'abbreviations.create',
            'abbreviations.manage',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'agent', 'guard_name' => 'web']);
        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $allPermissions = Permission::all();
        $superAdmin->syncPermissions($allPermissions);

        $managerPermissions = Permission::query()
            ->whereIn('name', User::defaultManagerPermissions())
            ->get();
        $manager->syncPermissions($managerPermissions);

        foreach (User::role('manager')->get() as $managerUser) {
            $managerUser->syncPermissions($managerPermissions);
        }

        $removedFromAgents = [
            'agents.create',
            'companies.create',
            'companies.manage',
        ];

        foreach (User::role('agent')->get() as $agent) {
            $agent->revokePermissionTo($removedFromAgents);

            if ($agent->can('folders.access') && ! $agent->hasPermissionTo('folders.edit')) {
                $agent->givePermissionTo('folders.edit');
            }
        }
    }
}
