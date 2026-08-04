<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShareController extends Controller
{
    public function show(Request $request, string $token): Response
    {
        $trip = Trip::query()
            ->where('share_token', $token)
            ->with(['locations', 'user', 'blocks'])
            ->firstOrFail();

        $ogImage = url('/og-share.jpg');
        $firstPhoto = $trip->locations->first()?->image_url;
        if ($firstPhoto && str_starts_with($firstPhoto, 'http')) {
            $ogImage = $firstPhoto;
        }

        return Inertia::render('Trip/Share', [
            'token' => $token,
            'flashSuccess' => $request->session()->get('success'),
            'trip' => [
                'name' => $trip->name,
                'visitor_name' => $trip->visitor_name,
                'host_name' => $trip->user?->name,
                'arrives_label' => $trip->arrives_at?->format('D j M · g:ia'),
                'departs_label' => $trip->departs_at?->format('D j M · g:ia'),
                'share_title' => $trip->shareTitle(),
                'share_blurb' => $trip->shareDescription(),
                'days' => $trip->days(),
                'locations' => $trip->locations->map(fn ($l) => [
                    'id' => $l->id,
                    'kind' => 'location',
                    'name' => $l->name,
                    'category' => $l->category,
                    'mode' => $l->mode,
                    'travel_time' => $l->travel_time,
                    'description' => $l->description,
                    'image_url' => $l->image_url,
                    'day_index' => $l->pivot->day_index,
                    'planned_time' => $l->pivot->planned_time
                        ? substr((string) $l->pivot->planned_time, 0, 5)
                        : null,
                    'notes' => $l->pivot->notes,
                ])->values(),
                'blocks' => $trip->blocks->map->toPlannerArray()->values(),
            ],
            'seo' => [
                'title' => $trip->shareTitle(),
                'description' => $trip->shareDescription(),
                'image' => $ogImage,
                'url' => route('share.show', $token),
            ],
            'categoryColors' => [
                'flying' => '#ff5a5f',
                'weekend' => '#f5a623',
                'local' => '#2e86de',
            ],
        ]);
    }
}
