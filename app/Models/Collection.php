<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Collection extends Model
{
    protected $fillable = [
        'private_token',
        'public_slug',
        'last_active_at'
    ];

    protected $casts = [
        'last_active_at' => 'datetime'
    ];

    public function items(): HasMany
    {
        return $this->hasMany(CollectionItem::class);
    }
}