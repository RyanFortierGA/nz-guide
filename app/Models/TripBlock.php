<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripBlock extends Model
{
    public const TYPES = ['meal', 'hangout', 'find', 'note'];

    public const TYPE_LABELS = [
        'meal' => 'Meal',
        'hangout' => 'Hang out',
        'find' => 'Location',
        'note' => 'Note',
    ];

    protected $fillable = [
        'trip_id',
        'type',
        'title',
        'notes',
        'link_url',
        'day_index',
        'planned_time',
        'sort_order',
        'added_by_name',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'day_index' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function typeLabel(): string
    {
        return self::TYPE_LABELS[$this->type] ?? ucfirst($this->type);
    }

    public function toPlannerArray(): array
    {
        return [
            'id' => $this->id,
            'kind' => 'block',
            'type' => $this->type,
            'type_label' => $this->typeLabel(),
            'title' => $this->title,
            'notes' => $this->notes,
            'link_url' => $this->link_url,
            'day_index' => $this->day_index,
            'planned_time' => $this->planned_time
                ? substr((string) $this->planned_time, 0, 5)
                : null,
            'sort_order' => $this->sort_order,
            'added_by_name' => $this->added_by_name,
            'source' => $this->source,
        ];
    }
}
