<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\SubLocation;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $payload = json_decode(
            file_get_contents(database_path('seeders/locations.json')),
            true
        );

        $slugToId = [];

        foreach ($payload['locations'] as $row) {
            $location = Location::updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'name' => $row['name'],
                    'category' => $row['category'],
                    'mode' => $row['mode'],
                    'travel_time' => $row['travel_time'],
                    'lat' => $row['lat'],
                    'lng' => $row['lng'],
                    'description' => $row['description'],
                    'best_time' => $row['best_time'],
                    'activities' => $row['activities'],
                    'image_url' => $row['image_url'],
                    'image_url_2' => $row['image_url_2'],
                    'airport_code' => $row['airport_code'],
                    'airbnb_query' => $row['airbnb_query'],
                    'maps_url' => $row['maps_url'] ?? null,
                    'is_published' => true,
                ]
            );
            $slugToId[$row['slug']] = $location->id;
        }

        SubLocation::query()->delete();

        foreach ($payload['sub_locations'] as $row) {
            if (! isset($slugToId[$row['parent']])) {
                continue;
            }

            SubLocation::create([
                'location_id' => $slugToId[$row['parent']],
                'name' => $row['name'],
                'lat' => $row['lat'],
                'lng' => $row['lng'],
                'image_url' => $row['image_url'],
                'maps_url' => $row['maps_url'] ?? null,
            ]);
        }
    }
}
