<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\TripBlock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TripBlockController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $trip = Trip::defaultFor($request->user());
        $maxDay = max(1, $trip->dayCount());

        $data = $this->validated($request, $maxDay);

        $trip->blocks()->create([
            ...$data,
            'sort_order' => ((int) $trip->blocks()->max('sort_order')) + 1,
            'source' => 'host',
            'added_by_name' => $request->user()->name,
        ]);

        return back()->with('success', 'Added to the plan.');
    }

    public function update(Request $request, TripBlock $block): RedirectResponse
    {
        $trip = Trip::defaultFor($request->user());
        abort_unless($block->trip_id === $trip->id, 403);

        $maxDay = max(1, $trip->dayCount());
        $data = $this->validated($request, $maxDay, updating: true);

        $block->update($data);

        return back();
    }

    public function destroy(Request $request, TripBlock $block): RedirectResponse
    {
        $trip = Trip::defaultFor($request->user());
        abort_unless($block->trip_id === $trip->id, 403);

        $block->delete();

        return back();
    }

    /** Guests on a share link can drop a location onto the trip. */
    public function suggest(Request $request, string $token): RedirectResponse
    {
        $trip = Trip::query()->where('share_token', $token)->firstOrFail();
        $maxDay = max(0, $trip->dayCount());

        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'notes' => ['nullable', 'string', 'max:800'],
            'link_url' => ['nullable', 'url', 'max:500'],
            'added_by_name' => ['nullable', 'string', 'max:80'],
            'day_index' => array_values(array_filter([
                'nullable',
                'integer',
                'min:1',
                $maxDay > 0 ? 'max:'.$maxDay : null,
            ])),
            'type' => ['nullable', Rule::in(TripBlock::TYPES)],
        ]);

        $trip->blocks()->create([
            'type' => $data['type'] ?? 'find',
            'title' => $data['title'],
            'notes' => $data['notes'] ?? null,
            'link_url' => $data['link_url'] ?? null,
            'day_index' => $data['day_index'] ?? null,
            'sort_order' => ((int) $trip->blocks()->max('sort_order')) + 1,
            'added_by_name' => $data['added_by_name'] ?: 'A guest',
            'source' => 'guest',
        ]);

        return back()->with('success', 'Nice — it’s on the plan now.');
    }

    private function validated(Request $request, int $maxDay, bool $updating = false): array
    {
        return $request->validate([
            'type' => [$updating ? 'sometimes' : 'required', Rule::in(TripBlock::TYPES)],
            'title' => [$updating ? 'sometimes' : 'required', 'string', 'max:160'],
            'notes' => ['nullable', 'string', 'max:800'],
            'link_url' => ['nullable', 'url', 'max:500'],
            'day_index' => ['nullable', 'integer', 'min:1', 'max:'.$maxDay],
            'planned_time' => ['nullable', 'date_format:H:i'],
        ]);
    }
}
