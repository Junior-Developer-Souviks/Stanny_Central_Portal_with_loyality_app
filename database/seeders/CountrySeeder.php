<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Country;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Country::insert([
            ['title' => 'India', 'created_at' => now(), 'updated_at' => now()],
            ['title' => 'South Africa', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
