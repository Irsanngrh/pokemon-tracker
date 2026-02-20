<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Card extends Model
{
    protected $fillable = ['expansion_id', 'name', 'card_number', 'rarity', 'image_url', 'category', 'illustrator', 'details'];
    protected $casts = ['details' => 'array'];

    public function expansion(): BelongsTo
    {
        return $this->belongsTo(Expansion::class);
    }

    public function price(): HasOne
    {
        return $this->hasOne(CardPrice::class)->latestOfMany();
    }

    public function prices(): HasMany
    {
        return $this->hasMany(CardPrice::class);
    }

    public function collectionItems(): HasMany
    {
        return $this->hasMany(CollectionItem::class);
    }
}