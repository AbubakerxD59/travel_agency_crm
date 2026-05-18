<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Destination;
use App\Models\Folder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Folder>
 */
class FolderFactory extends Factory
{
    protected $model = Folder::class;

    public function definition(): array
    {
        $travelDate = Carbon::instance(fake()->dateTimeBetween('+1 day', '+30 days'))->startOfDay();

        return [
            'agent_id' => User::factory(),
            'order_type' => fake()->randomElement(folder_order_types()),
            'vendor_reference' => 'FAC-'.fake()->unique()->numerify('######'),
            'customer_name' => fake()->name(),
            'company_id' => Company::factory(),
            'destination_id' => Destination::factory(),
            'travel_date' => $travelDate,
            'balance_due_date' => $travelDate->copy()->subDays(7),
            'makkah_ziarat' => fake()->boolean(40),
            'madinah_ziarat' => fake()->boolean(40),
        ];
    }

    public function travelDaysFromToday(int $days): static
    {
        return $this->state(function () use ($days) {
            $travelDate = now()->startOfDay()->addDays($days);

            return [
                'travel_date' => $travelDate,
                'balance_due_date' => $travelDate->copy()->subDays(7),
            ];
        });
    }

    public function upcoming(): static
    {
        return $this->travelDaysFromToday(fake()->numberBetween(1, 20));
    }
}
