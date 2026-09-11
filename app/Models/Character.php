<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Character extends Model
{
    protected $fillable = [
        'external_id',
        'name',
        'status',
        'species',
        'type',
        'gender',
        'image',
        'origin_location_id',
        'current_location_id',
    ];
    public function episodes(): BelongsToMany
    {
        return $this->belongsToMany(Episode::class);
    }

    public function origin(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'origin_location_id');
    }

    public function current(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'current_location_id');
    }
}
