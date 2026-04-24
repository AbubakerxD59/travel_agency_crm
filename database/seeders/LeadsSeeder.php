<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Seeder;

class LeadsSeeder extends Seeder
{
    public function run(): void
    {
        $agents = User::role('agent')->get();
        if ($agents->isEmpty()) {
            $this->call(AgentsSeeder::class);
            $agents = User::role('agent')->get();
        }

        $companies = Company::query()->get();
        if ($companies->isEmpty()) {
            $this->call(CompaniesSeeder::class);
            $companies = Company::query()->get();
        }

        $totalLeadsToCreate = 350;
        $now = now();
        $currentYear = (int) $now->year;
        $currentMonth = (int) $now->month;

        foreach (range(1, $totalLeadsToCreate) as $index) {
            $month = ($index % $currentMonth) + 1;
            $monthStart = now()->setDate($currentYear, $month, 1)->startOfDay();
            $monthEnd = $month === $currentMonth
                ? (clone $now)
                : (clone $monthStart)->endOfMonth();
            $createdAt = fake()->dateTimeBetween($monthStart, $monthEnd);
            $updatedAt = (clone $createdAt)->modify('+'.random_int(0, 10).' days');

            if ($updatedAt > $now) {
                $updatedAt = clone $now;
            }

            Lead::query()->create([
                'agent_id' => $agents->random()->id,
                'customer_name' => fake()->name(),
                'phone_number' => fake()->phoneNumber(),
                'email' => fake()->safeEmail(),
                'city' => fake()->city(),
                'source' => fake()->randomElement(['meta', 'google', 'whatsapp', 'referral']),
                'status' => fake()->randomElement(Lead::statusKeys()),
                'notes' => fake()->sentence(),
                'company_id' => $companies->random()->id,
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ]);
        }
    }
}
