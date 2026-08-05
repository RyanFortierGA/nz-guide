<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Trip extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'visitor_name',
        'arrives_at',
        'departs_at',
        'share_token',
        'share_blurb',
        'setup_complete',
        'party_size',
        'include_auckland_stay',
        'auckland_airbnb_night',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'setup_complete' => 'boolean',
            'include_auckland_stay' => 'boolean',
            'arrives_at' => 'datetime',
            'departs_at' => 'datetime',
            'party_size' => 'integer',
            'auckland_airbnb_night' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'trip_location')
            ->withPivot(['id', 'sort_order', 'notes', 'day_index', 'planned_time', 'nights'])
            ->withTimestamps()
            ->orderByPivot('day_index')
            ->orderByPivot('planned_time')
            ->orderByPivot('sort_order');
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(TripBlock::class)
            ->orderBy('day_index')
            ->orderBy('planned_time')
            ->orderBy('sort_order');
    }

    public static function defaultFor(User $user): self
    {
        $trip = static::firstOrCreate(
            ['user_id' => $user->id, 'is_default' => true],
            ['name' => 'My Trip']
        );

        if (! $trip->share_token) {
            $trip->forceFill(['share_token' => Str::random(40)])->save();
        }

        return $trip;
    }

    public function ensureShareToken(): string
    {
        if (! $this->share_token) {
            $this->forceFill(['share_token' => Str::random(40)])->save();
        }

        return $this->share_token;
    }

    public function dayCount(): int
    {
        if (! $this->arrives_at || ! $this->departs_at) {
            return 0;
        }

        $start = $this->arrives_at->copy()->startOfDay();
        $end = $this->departs_at->copy()->startOfDay();

        if ($end->lt($start)) {
            return 0;
        }

        return $start->diffInDays($end) + 1;
    }

    /**
     * @return list<array{index:int, date:string, label:string, is_arrival:bool, is_departure:bool}>
     */
    public function days(): array
    {
        $count = $this->dayCount();
        if ($count < 1) {
            return [];
        }

        $days = [];
        $cursor = $this->arrives_at->copy()->startOfDay();

        for ($i = 1; $i <= $count; $i++) {
            $days[] = [
                'index' => $i,
                'date' => $cursor->toDateString(),
                'label' => $cursor->format('D j M'),
                'is_arrival' => $i === 1,
                'is_departure' => $i === $count,
                'arrival_time' => $i === 1 ? $this->arrives_at->format('H:i') : null,
                'departure_time' => $i === $count ? $this->departs_at->format('H:i') : null,
            ];
            $cursor->addDay();
        }

        return $days;
    }

    public function shareTitle(): string
    {
        $who = $this->visitor_name ?: 'you';

        return "Ideas for {$who} — visiting us in Auckland";
    }

    public function shareDescription(): string
    {
        if ($this->share_blurb) {
            return $this->share_blurb;
        }

        $host = $this->user?->name ?: 'us';
        $span = '';

        if ($this->arrives_at && $this->departs_at) {
            $span = ' '.$this->arrives_at->format('j M').' → '.$this->departs_at->format('j M Y').'.';
        }

        return "A little guide from {$host} for your visit to Aotearoa.{$span} Places near home, weekend drives, and a few further afield — no bookings pressure, just ideas.";
    }
}
