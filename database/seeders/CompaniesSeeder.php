<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Country;
use Illuminate\Database\Seeder;

class CompaniesSeeder extends Seeder
{
    public function run(): void
    {
        $countries = Country::query()->get();

        if ($countries->isEmpty()) {
            $this->call(CountrySeeder::class);
            $countries = Country::query()->get();
        }

        foreach (range(1, 10) as $index) {
            Company::query()->create([
                'name' => fake()->unique()->company().' '.$index,
                'country_id' => $countries->random()->id,
            ]);
        }
    }
}
