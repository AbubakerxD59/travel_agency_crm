<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Destination;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Seeder;

class LeadsSeeder extends Seeder
{
    public function run(): void
    {
        $destinations = Destination::query()->get();

        if ($destinations->isEmpty()) {
            $this->call(DestinationSeeder::class);
            $destinations = Destination::query()->get();
        }

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
        $successfulCount = 90;
        $failedCount = 120;
        $remainingCount = $totalLeadsToCreate - $successfulCount - $failedCount;
        $otherStatuses = [
            Lead::STATUS_NEW,
            Lead::STATUS_CONTACTED,
            Lead::STATUS_FOLLOW_UP,
        ];

        $statuses = array_merge(
            array_fill(0, $successfulCount, Lead::STATUS_SALE_DONE),
            array_fill(0, $failedCount, Lead::STATUS_NOT_CONVERTED),
            collect(range(1, $remainingCount))
                ->map(fn (): string => fake()->randomElement($otherStatuses))
                ->all()
        );

        shuffle($statuses);

        $now = now();
        $currentYear = (int) $now->year;
        $currentMonth = (int) $now->month;

        foreach ($statuses as $index => $status) {
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
                'source' => fake()->randomElement(['WhatsApp', 'Phone', 'Referral', 'Website']),
                'notes' => fake()->sentence(),
                'order_type' => fake()->randomElement(['umrah', 'hajj', 'ticketing', 'visa']),
                'vendor_reference' => fake()->optional()->bothify('REF-####'),
                'company_id' => $companies->random()->id,
                'status' => $status,
                'destination_id' => $destinations->random()->id,
                'travel_date' => fake()->dateTimeBetween('+1 week', '+5 months')->format('Y-m-d'),
                'balance_due_date' => fake()->boolean(70)
                    ? fake()->dateTimeBetween('now', '+2 months')->format('Y-m-d')
                    : null,
                'flight_itinerary' => fake()->optional()->sentence(),
                'ziarat_makkah' => fake()->boolean(),
                'ziarat_madinah' => fake()->boolean(),
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ]);
        }
    }
}
