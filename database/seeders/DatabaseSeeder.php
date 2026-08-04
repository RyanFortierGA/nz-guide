<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'demo@aotearoa.test'],
            [
                'name' => 'Demo Traveller',
                'password' => Hash::make('password'),
                'home_name' => '403/1 Greys Ave, Auckland 1010',
                'home_lat' => -36.8527018,
                'home_lng' => 174.7621745,
                'home_airport' => 'AKL',
            ]
        );

        $this->call(LocationSeeder::class);
    }
}
