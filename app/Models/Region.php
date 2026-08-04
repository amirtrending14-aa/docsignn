<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    protected $fillable = ['name_tj', 'name_ru'];

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }
}