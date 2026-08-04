<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class LocationController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Locations/Create', [
            'categories' => [
                'flying' => 'Flying',
                'weekend' => 'Weekend Trips',
                'local' => 'In Auckland',
            ],
            'modes' => [
                'plane' => 'Plane',
                'car' => 'Car',
                'ferry' => 'Ferry',
                'walk' => 'Walk',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'category' => ['required', Rule::in(['flying', 'weekend', 'local'])],
            'mode' => ['required', Rule::in(['plane', 'car', 'ferry', 'walk'])],
            'travel_time' => ['required', 'string', 'max:40'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'description' => ['required', 'string', 'max:1000'],
            'best_time' => ['nullable', 'string', 'max:200'],
            'activities' => ['nullable', 'string', 'max:1000'],
            'image_url' => ['nullable', 'url', 'max:2000'],
            'image_url_2' => ['nullable', 'url', 'max:2000'],
            'airport_code' => ['nullable', 'string', 'max:8'],
            'airbnb_query' => ['nullable', 'string', 'max:120'],
        ]);

        $slug = Str::slug($data['name']);
        $base = $slug;
        $i = 1;
        while (Location::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        $activities = collect(preg_split('/[\n,]+/', $data['activities'] ?? ''))
            ->map(fn ($a) => trim($a))
            ->filter()
            ->values()
            ->all();

        Location::create([
            ...collect($data)->except('activities')->all(),
            'slug' => $slug,
            'activities' => $activities,
            'airport_code' => isset($data['airport_code']) ? strtoupper($data['airport_code']) : null,
            'airbnb_query' => $data['airbnb_query'] ?: $data['name'],
            'created_by' => $request->user()->id,
            'is_published' => true,
        ]);

        return redirect()->route('explore')->with('success', 'Location added.');
    }
}
