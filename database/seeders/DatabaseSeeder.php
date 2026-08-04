<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->create([
            'name' => 'Demo Traveller',
            'email' => 'demo@aotearoa.test',
            'password' => 'password',
            'home_name' => '403/1 Greys Ave, Auckland 1010',
            'home_lat' => -36.8527018,
            'home_lng' => 174.7621745,
            'home_airport' => 'AKL',
        ]);

        $this->call(LocationSeeder::class);
    }
}
