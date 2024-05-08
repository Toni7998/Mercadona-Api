<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'name',
    ];

    public function priceHistories(): HasMany
    {
        return $this->hasMany(PriceHistory::class);
    }
}
