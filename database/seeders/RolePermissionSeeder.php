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
            'folders.access',
            'companies.create',
            'companies.manage',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'agent', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());
    }
}
