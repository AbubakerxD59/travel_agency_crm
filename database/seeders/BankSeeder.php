<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Al Rajhi Bank',
            'Saudi National Bank',
            'Riyad Bank',
            'Bank Albilad',
        ];

        foreach ($names as $name) {
            Bank::query()->firstOrCreate(['name' => $name]);
        }
    }
}
