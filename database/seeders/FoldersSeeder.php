<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Destination;
use App\Models\Folder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class FoldersSeeder extends Seeder
{
    private const TOTAL = 500;

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

        $destinations = Destination::query()->get();
        if ($destinations->isEmpty()) {
            $this->call(DestinationSeeder::class);
            $destinations = Destination::query()->get();
        }

        Folder::factory()
            ->count(self::TOTAL)
            ->create(function () use ($agents, $companies, $destinations) {
                $travelDate = Carbon::instance(
                    fake()->dateTimeBetween('-6 months', '+3 months')
                )->startOfDay();

                return [
                    'agent_id' => $agents->random()->id,
                    'company_id' => $companies->random()->id,
                    'destination_id' => $destinations->random()->id,
                    'travel_date' => $travelDate,
                    'balance_due_date' => $travelDate->copy()->subDays(7),
                ];
            });

        $this->command?->info('FoldersSeeder: created '.self::TOTAL.' dummy folder(s).');
    }
}
