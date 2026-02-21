<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Expansion extends Model {
    protected $fillable = ['name', 'total_cards'];

    public function cards(): HasMany {
        return $this->hasMany(Card::class);
    }
}