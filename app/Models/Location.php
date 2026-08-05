<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'category',
        'mode',
        'travel_time',
        'lat',
        'lng',
        'description',
        'best_time',
        'activities',
        'image_url',
        'image_url_2',
        'airport_code',
        'airbnb_query',
        'maps_url',
        'cost_flight_pp',
        'cost_airbnb_night',
        'cost_day_pp',
        'cost_suggested_nights',
        'cost_transport',
        'created_by',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'activities' => 'array',
            'lat' => 'float',
            'lng' => 'float',
            'is_published' => 'boolean',
            'cost_flight_pp' => 'integer',
            'cost_airbnb_night' => 'integer',
            'cost_day_pp' => 'integer',
            'cost_suggested_nights' => 'integer',
            'cost_transport' => 'integer',
        ];
    }

    public function subLocations(): HasMany
    {
        return $this->hasMany(SubLocation::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function trips(): BelongsToMany
    {
        return $this->belongsToMany(Trip::class, 'trip_location')
            ->withPivot(['sort_order', 'notes'])
            ->withTimestamps();
    }

    public function mapsUrl(): string
    {
        if ($this->maps_url) {
            return $this->maps_url;
        }

        return 'https://www.google.com/maps/search/?api=1&query='.urlencode("{$this->lat},{$this->lng}");
    }

    public function airbnbSearchUrl(): string
    {
        $query = urlencode($this->airbnb_query ?: $this->name);

        return "https://www.airbnb.com/s/{$query}/homes?tab_id=home_tab&refinement_paths%5B%5D=%2Fhomes&query={$query}&place_id=&search_type=search_query&lat={$this->lat}&lng={$this->lng}";
    }

    public function googleFlightsUrl(?string $originAirport = 'AKL'): ?string
    {
        if (! $this->airport_code || $this->category !== 'flying') {
            return null;
        }

        $origin = $originAirport ?: 'AKL';
        $dest = $this->airport_code;

        return "https://www.google.com/travel/flights?q=Flights%20to%20{$dest}%20from%20{$origin}";
    }
}
