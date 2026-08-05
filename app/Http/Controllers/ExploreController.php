<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Trip;
use App\Services\TripCostEstimator;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExploreController extends Controller
{
    public function index(Request $request): Response
    {
        $estimator = app(TripCostEstimator::class);
        $party = 2;

        $trip = null;
        $tripLocationIds = [];

        if ($request->user()) {
            $tripModel = Trip::defaultFor($request->user());
            $tripModel->ensureShareToken();
            $tripModel->load(['locations' => fn ($q) => $q->with('subLocations')]);
            $party = max(1, (int) ($tripModel->party_size ?: 2));
            $tripLocationIds = $tripModel->locations->pluck('id')->all();
            $trip = [
                'id' => $tripModel->id,
                'name' => $tripModel->name,
                'visitor_name' => $tripModel->visitor_name,
                'setup_complete' => $tripModel->setup_complete,
                'arrives_label' => $tripModel->arrives_at?->format('j M'),
                'departs_label' => $tripModel->departs_at?->format('j M'),
                'share_url' => route('share.show', $tripModel->share_token),
                'locations' => $tripModel->locations->map(fn (Location $l) => $this->serializeLocation($l, $estimator, $party))->values(),
                'cost_summary' => $estimator->forTrip($tripModel),
            ];
        }

        $locations = Location::query()
            ->where('is_published', true)
            ->with('subLocations')
            ->orderByRaw("CASE category WHEN 'flying' THEN 1 WHEN 'weekend' THEN 2 WHEN 'local' THEN 3 ELSE 4 END")
            ->orderBy('name')
            ->get()
            ->map(fn (Location $location) => $this->serializeLocation($location, $estimator, $party));

        $home = $this->homeBase($request);

        return Inertia::render('Explore/Index', [
            'locations' => $locations,
            'trip' => $trip,
            'tripLocationIds' => $tripLocationIds,
            'home' => $home,
            'seo' => [
                'title' => 'Come visit — Auckland ideas for friends & family',
                'description' => 'A little personal guide to Auckland and beyond from our place. Walks, ferries, weekend drives, and a few flights further afield — for people coming to stay with us.',
                'image' => url('/og-share.jpg'),
                'url' => url('/'),
            ],
            'categories' => [
                'local' => 'In Auckland',
                'weekend' => 'Weekend Trips',
                'flying' => 'Flying',
                'all' => 'All Distances',
            ],
            'categoryColors' => [
                'flying' => '#ff5a5f',
                'weekend' => '#f5a623',
                'local' => '#2e86de',
            ],
        ]);
    }

    private function homeBase(Request $request): array
    {
        $user = $request->user();

        if ($user?->home_lat && $user?->home_lng) {
            return [
                'name' => $user->home_name ?: 'Home',
                'lat' => $user->home_lat,
                'lng' => $user->home_lng,
                'airport' => $user->home_airport ?: 'AKL',
            ];
        }

        return [
            'name' => '403/1 Greys Ave, Auckland 1010',
            'lat' => -36.8527018,
            'lng' => 174.7621745,
            'airport' => 'AKL',
        ];
    }

    private function serializeLocation(Location $location, TripCostEstimator $estimator, int $party = 2): array
    {
        return [
            'id' => $location->id,
            'slug' => $location->slug,
            'name' => $location->name,
            'category' => $location->category,
            'mode' => $location->mode,
            'travel_time' => $location->travel_time,
            'lat' => $location->lat,
            'lng' => $location->lng,
            'description' => $location->description,
            'best_time' => $location->best_time,
            'activities' => $location->activities ?? [],
            'image_url' => $location->image_url,
            'image_url_2' => $location->image_url_2,
            'airport_code' => $location->airport_code,
            'maps_url' => $location->mapsUrl(),
            'airbnb_url' => $location->airbnbSearchUrl(),
            'flights_url' => $location->googleFlightsUrl(
                request()->user()?->home_airport ?: 'AKL'
            ),
            'cost_estimate' => $estimator->forLocationAddOn($location, $party),
            'sub_locations' => $location->relationLoaded('subLocations')
                ? $location->subLocations->map(fn ($s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'lat' => $s->lat,
                    'lng' => $s->lng,
                    'image_url' => $s->image_url,
                    'maps_url' => $s->mapsUrl(),
                ])->values()
                : [],
        ];
    }
}
