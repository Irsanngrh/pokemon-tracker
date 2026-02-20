<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardPrice extends Model
{
    protected $fillable = [
        'card_id',
        'price',
        'source_url',
        'is_manual_override'
    ];

    protected $casts = [
        'is_manual_override' => 'boolean',
        'price' => 'decimal:2'
    ];

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }
}