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
    private const TOTAL_FOLDERS = 50;

    private const UPCOMING_FOLDERS = 15;

    private const UPCOMING_WINDOW_DAYS = 20;

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

        if ($companies->isEmpty() || $destinations->isEmpty()) {
            $this->command?->warn('FoldersSeeder skipped: companies or destinations are required.');

            return;
        }

        $orderTypes = folder_order_types();
        $today = now()->startOfDay();

        foreach (range(1, self::TOTAL_FOLDERS) as $index) {
            $isUpcoming = $index <= self::UPCOMING_FOLDERS;
            $travelDate = $this->travelDateForFolder($index, $isUpcoming, $today);

            $folder = Folder::query()->create([
                'agent_id' => $agents->isNotEmpty() ? $agents->random()->id : null,
                'order_type' => fake()->randomElement($orderTypes),
                'vendor_reference' => 'VF-'.str_pad((string) $index, 5, '0', STR_PAD_LEFT),
                'customer_name' => fake()->name(),
                'company_id' => $companies->random()->id,
                'destination_id' => $destinations->random()->id,
                'travel_date' => $travelDate->toDateString(),
                'booking_date' => $travelDate->copy()->subDays(fake()->numberBetween(14, 45))->toDateString(),
                'balance_due_date' => $travelDate->copy()->subDays(fake()->numberBetween(7, 30))->toDateString(),
                'makkah_ziarat' => fake()->boolean(60),
                'madinah_ziarat' => fake()->boolean(60),
            ]);

            $this->seedItinerary($folder, $travelDate);
            $this->seedPassenger($folder);
            $this->seedHotelDetail($folder);
        }
    }

    private function travelDateForFolder(int $index, bool $isUpcoming, Carbon $today): Carbon
    {
        if ($isUpcoming) {
            $dayOffset = (($index - 1) % self::UPCOMING_WINDOW_DAYS) + 1;

            return $today->copy()->addDays($dayOffset);
        }

        if ($index % 2 === 0) {
            return $today->copy()->subDays(fake()->numberBetween(21, 180));
        }

        return $today->copy()->addDays(fake()->numberBetween(self::UPCOMING_WINDOW_DAYS + 1, 120));
    }

    private function seedItinerary(Folder $folder, Carbon $travelDate): void
    {
        $arrivalDate = $travelDate->copy()->addDay();

        $folder->itineraries()->create([
            'sr_no' => 1,
            'airline_code' => fake()->randomElement(['SV', 'PK', 'EK', 'QR']),
            'airline_number' => (string) fake()->numberBetween(100, 999),
            'class' => fake()->randomElement(['Economy', 'Business']),
            'departure_date' => $travelDate->toDateString(),
            'departure_airport' => fake()->randomElement(['LHE', 'ISB', 'KHI', 'DXB']),
            'arrival_airport' => fake()->randomElement(['JED', 'MED', 'RUH']),
            'departure_time' => fake()->time('H:i'),
            'arrival_date' => $arrivalDate->toDateString(),
            'arrival_time' => fake()->time('H:i'),
        ]);
    }

    private function seedPassenger(Folder $folder): void
    {
        $folder->passengers()->create([
            'title' => fake()->randomElement(folder_passenger_titles()),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'passenger_type' => fake()->randomElement(folder_passenger_types()),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
        ]);
    }

    private function seedHotelDetail(Folder $folder): void
    {
        $dateIn = $folder->travel_date?->copy() ?? now();
        $nights = fake()->numberBetween(3, 10);

        $folder->hotelDetails()->create([
            'sr_no' => 1,
            'supplier' => fake()->company(),
            'hotel_name' => fake()->randomElement(['Hilton Makkah', 'Swissotel Al Maqam', 'Dar Al Eiman', 'Madinah Oberoi']),
            'guest_name' => $folder->customer_name,
            'rooms' => (string) fake()->numberBetween(1, 3),
            'type' => 'Double',
            'meals' => 'Half-Board',
            'date_in' => $dateIn->toDateString(),
            'date_out' => $dateIn->copy()->addDays($nights)->toDateString(),
            'nights' => $nights,
            'status' => fake()->randomElement(['confirmed', 'issue_later']),
            'cost' => fake()->randomFloat(2, 500, 5000),
            'sell' => fake()->randomFloat(2, 800, 7000),
            'hotel_city' => fake()->randomElement(['Makkah', 'Madinah']),
        ]);
    }
}
