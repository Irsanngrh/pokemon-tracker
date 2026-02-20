<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionItem extends Model
{
    protected $fillable = [
        'collection_id',
        'card_id',
        'is_owned',
        'is_wishlist',
        'quantity'
    ];

    protected $casts = [
        'is_owned' => 'boolean',
        'is_wishlist' => 'boolean',
        'quantity' => 'integer'
    ];

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }
}