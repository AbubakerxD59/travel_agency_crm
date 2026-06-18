<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'HSBC / Account 01114646 Sort 40-11-56',
            'Monzo Card Payment',
        ];

        foreach ($names as $name) {
            Bank::query()->firstOrCreate(['name' => $name]);
        }
    }
}
