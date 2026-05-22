<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Role::firstOrCreate([
            'name' => User::ROLE_AGENT,
            'guard_name' => 'web',
        ]);

        Permission::firstOrCreate([
            'name' => 'leads.create',
            'guard_name' => 'web',
        ]);

        $this->agents()->each(function (User $agent): void {
            if (! $agent->hasPermissionTo('leads.create')) {
                $agent->givePermissionTo('leads.create');
            }
        });
    }

    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->agents()->each(function (User $agent): void {
            if ($agent->hasPermissionTo('leads.create')) {
                $agent->revokePermissionTo('leads.create');
            }
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<User>
     */
    private function agents()
    {
        $role = Role::query()
            ->where('name', User::ROLE_AGENT)
            ->where('guard_name', 'web')
            ->first();

        if ($role === null) {
            return User::query()->whereRaw('0 = 1');
        }

        return User::role(User::ROLE_AGENT);
    }
};
