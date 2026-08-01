<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'venue_name',
        'address',
        'latitude',
        'longitude',
        'event_date',
        'is_live',
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'is_live' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function media(): HasMany
    {
        return $this->hasMany(EventMedia::class);
    }

    public function isPast(): bool
    {
        return ! $this->is_live && $this->event_date->isPast();
    }
}
