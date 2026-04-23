<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AgentsSeeder extends Seeder
{
    public function run(): void
    {
        $agents = User::factory()->count(10)->create();
        $agents->each(fn (User $agent) => $agent->assignRole('agent'));
    }
}
