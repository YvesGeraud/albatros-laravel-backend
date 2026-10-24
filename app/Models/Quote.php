<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quote extends Model
{
    protected $fillable = [
        'customer_name',
        'customer_email',
        'customer_phone',
        'package',
        'event_date',
        'guests',
        'event_location',
        'venue_type',
        'notes',
        'total',
        'status',
    ];

    protected $casts = [
        'event_date' => 'date',
        'guests' => 'integer',
        'total' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }
}
