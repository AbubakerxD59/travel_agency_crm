<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => RolePermissionSeeder::SUPER_ADMIN_EMAIL],
            [
                'name' => 'Super Admin',
                'password' => Hash::make(RolePermissionSeeder::SUPER_ADMIN_PASSWORD),
            ],
        );

        $user->assignRole('super-admin');
    }
}
