<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permission = Permission::firstOrCreate([
            'name' => 'leads.export',
            'guard_name' => 'web',
        ]);

        $superAdmin = Role::firstOrCreate([
            'name' => 'super-admin',
            'guard_name' => 'web',
        ]);

        $superAdmin->givePermissionTo($permission);
    }

    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permission = Permission::query()
            ->where('name', 'leads.export')
            ->where('guard_name', 'web')
            ->first();

        if ($permission !== null) {
            Role::query()
                ->where('name', 'super-admin')
                ->where('guard_name', 'web')
                ->first()
                ?->revokePermissionTo($permission);

            $permission->delete();
        }
    }
};
