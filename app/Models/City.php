<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    protected $fillable = ['region_id', 'name_tj', 'name_ru'];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function organizations(): HasMany
    {
        return $this->hasMany(Organization::class);
    }
}