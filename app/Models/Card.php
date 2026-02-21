<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Card extends Model {
    protected $fillable = ['expansion_id', 'official_id', 'name', 'card_number', 'rarity', 'image_url', 'category', 'illustrator', 'details'];
    protected $casts = ['details' => 'array'];

    public function expansion(): BelongsTo {
        return $this->belongsTo(Expansion::class);
    }
}