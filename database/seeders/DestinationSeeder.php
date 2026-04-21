<?php

namespace Database\Seeders;

use App\Models\Destination;
use Illuminate\Database\Seeder;

class DestinationSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Makkah', 'Madinah', 'Jeddah'] as $name) {
            Destination::firstOrCreate(['name' => $name]);
        }
    }
}
