<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubLocation extends Model
{
    protected $fillable = [
        'location_id',
        'name',
        'lat',
        'lng',
        'image_url',
        'maps_url',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'float',
            'lng' => 'float',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function mapsUrl(): string
    {
        if ($this->maps_url) {
            return $this->maps_url;
        }

        return 'https://www.google.com/maps/search/?api=1&query='.urlencode("{$this->name}@{$this->lat},{$this->lng}");
    }
}
