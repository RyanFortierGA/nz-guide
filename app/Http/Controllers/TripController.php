<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Trip;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TripController extends Controller
{
    public function show(Request $request): Response|RedirectResponse
    {
        $trip = Trip::defaultFor($request->user());

        if (! $trip->setup_complete || ! $trip->arrives_at || ! $trip->departs_at) {
            return redirect()->route('trip.setup');
        }

        return Inertia::render('Trip/Planner', $this->plannerProps($trip));
    }

    public function setup(Request $request): Response
    {
        $trip = Trip::defaultFor($request->user());

        return Inertia::render('Trip/Setup', [
            'trip' => [
                'id' => $trip->id,
                'name' => $trip->name,
                'visitor_name' => $trip->visitor_name,
                'arrives_at' => $trip->arrives_at?->format('Y-m-d\TH:i'),
                'departs_at' => $trip->departs_at?->format('Y-m-d\TH:i'),
                'share_blurb' => $trip->share_blurb,
                'setup_complete' => $trip->setup_complete,
            ],
        ]);
    }

    public function saveSetup(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'visitor_name' => ['nullable', 'string', 'max:120'],
            'arrives_at' => ['required', 'date'],
            'departs_at' => ['required', 'date', 'after:arrives_at'],
            'share_blurb' => ['nullable', 'string', 'max:280'],
            'name' => ['nullable', 'string', 'max:120'],
        ]);

        $trip = Trip::defaultFor($request->user());
        $trip->fill([
            'visitor_name' => $data['visitor_name'] ?: null,
            'arrives_at' => $data['arrives_at'],
            'departs_at' => $data['departs_at'],
            'share_blurb' => $data['share_blurb'] ?: null,
            'name' => $data['name'] ?: ($data['visitor_name'] ? $data['visitor_name']."'s visit" : 'My Trip'),
            'setup_complete' => true,
        ]);
        $trip->ensureShareToken();
        $trip->save();

        return redirect()->route('trip.show')->with('success', 'Dates saved — now drop places onto days.');
    }

    public function add(Request $request, Location $location): RedirectResponse
    {
        $trip = Trip::defaultFor($request->user());

        if (! $trip->locations()->where('location_id', $location->id)->exists()) {
            $max = (int) $trip->locations()->max('trip_location.sort_order');
            $trip->locations()->attach($location->id, [
                'sort_order' => $max + 1,
                'day_index' => null,
            ]);
        }

        return back();
    }

    public function remove(Request $request, Location $location): RedirectResponse
    {
        $trip = Trip::defaultFor($request->user());
        $trip->locations()->detach($location->id);

        return back();
    }

    public function reorder(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'location_ids' => ['required', 'array'],
            'location_ids.*' => ['integer', 'exists:locations,id'],
        ]);

        $trip = Trip::defaultFor($request->user());

        foreach ($data['location_ids'] as $index => $locationId) {
            $trip->locations()->updateExistingPivot($locationId, [
                'sort_order' => $index + 1,
            ]);
        }

        return back();
    }

    public function assignDay(Request $request, Location $location): RedirectResponse
    {
        $trip = Trip::defaultFor($request->user());
        $maxDay = max(1, $trip->dayCount());

        $data = $request->validate([
            'day_index' => ['nullable', 'integer', 'min:1', 'max:'.$maxDay],
            'planned_time' => ['nullable', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:500'],
            'nights' => ['nullable', 'integer', 'min:0', 'max:30'],
        ]);

        if (! $trip->locations()->where('location_id', $location->id)->exists()) {
            $max = (int) $trip->locations()->max('trip_location.sort_order');
            $trip->locations()->attach($location->id, [
                'sort_order' => $max + 1,
                'day_index' => $data['day_index'] ?? null,
                'planned_time' => $data['planned_time'] ?? null,
                'notes' => $data['notes'] ?? null,
                'nights' => $data['nights'] ?? null,
            ]);
        } else {
            $existing = $trip->locations()->where('location_id', $location->id)->first();
            $payload = [];
            foreach (['day_index', 'planned_time', 'notes', 'nights'] as $field) {
                if (array_key_exists($field, $data)) {
                    $payload[$field] = $data[$field];
                } else {
                    $payload[$field] = $existing?->pivot?->{$field};
                }
            }
            $trip->locations()->updateExistingPivot($location->id, $payload);
        }

        return back();
    }

    public function updateCosts(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'party_size' => ['required', 'integer', 'min:1', 'max:12'],
            'include_auckland_stay' => ['sometimes', 'boolean'],
            'auckland_airbnb_night' => ['nullable', 'integer', 'min:50', 'max:800'],
        ]);

        $trip = Trip::defaultFor($request->user());
        $trip->fill([
            'party_size' => $data['party_size'],
            'include_auckland_stay' => $request->boolean('include_auckland_stay', $trip->include_auckland_stay),
            'auckland_airbnb_night' => $data['auckland_airbnb_night'] ?? $trip->auckland_airbnb_night,
        ])->save();

        return back();
    }

    private function plannerProps(Trip $trip): array
    {
        $trip->load(['locations' => fn ($q) => $q->with('subLocations'), 'user', 'blocks']);
        $estimator = app(\App\Services\TripCostEstimator::class);

        return [
            'trip' => [
                'id' => $trip->id,
                'name' => $trip->name,
                'visitor_name' => $trip->visitor_name,
                'arrives_at' => $trip->arrives_at?->toIso8601String(),
                'departs_at' => $trip->departs_at?->toIso8601String(),
                'arrives_label' => $trip->arrives_at?->format('D j M · H:i'),
                'departs_label' => $trip->departs_at?->format('D j M · H:i'),
                'share_token' => $trip->ensureShareToken(),
                'share_url' => route('share.show', $trip->share_token),
                'share_title' => $trip->shareTitle(),
                'share_blurb' => $trip->shareDescription(),
                'setup_complete' => $trip->setup_complete,
                'party_size' => $trip->party_size ?: 2,
                'include_auckland_stay' => (bool) $trip->include_auckland_stay,
                'auckland_airbnb_night' => $trip->auckland_airbnb_night ?: 180,
                'days' => $trip->days(),
                'locations' => $trip->locations->map(fn (Location $l) => [
                    'id' => $l->id,
                    'kind' => 'location',
                    'slug' => $l->slug,
                    'name' => $l->name,
                    'category' => $l->category,
                    'mode' => $l->mode,
                    'travel_time' => $l->travel_time,
                    'description' => $l->description,
                    'image_url' => $l->image_url,
                    'lat' => $l->lat,
                    'lng' => $l->lng,
                    'day_index' => $l->pivot->day_index,
                    'planned_time' => $l->pivot->planned_time
                        ? substr((string) $l->pivot->planned_time, 0, 5)
                        : null,
                    'notes' => $l->pivot->notes,
                    'nights' => $l->pivot->nights,
                    'suggested_nights' => $l->cost_suggested_nights,
                    'sort_order' => $l->pivot->sort_order,
                    'cost_preview' => $estimator->forLocationAddOn(
                        $l,
                        $trip->party_size ?: 2,
                        $l->pivot->nights !== null ? (int) $l->pivot->nights : null
                    ),
                ])->values(),
                'blocks' => $trip->blocks->map->toPlannerArray()->values(),
            ],
            'costs' => $estimator->forTrip($trip),
            'blockTypes' => \App\Models\TripBlock::TYPE_LABELS,
            'categoryColors' => [
                'flying' => '#ff5a5f',
                'weekend' => '#f5a623',
                'local' => '#2e86de',
            ],
        ];
    }
}
