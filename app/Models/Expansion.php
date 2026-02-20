<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Expansion extends Model
{
    protected $fillable = [
        'name',
        'release_date',
        'symbol_image_url',
        'total_cards'
    ];

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }
}