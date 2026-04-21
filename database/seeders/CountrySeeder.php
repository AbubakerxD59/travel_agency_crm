<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['USA', 'UK'] as $name) {
            Country::firstOrCreate(['name' => $name]);
        }
    }
}
