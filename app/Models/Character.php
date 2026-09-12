<?php

namespace App\Models;

use App\Enums\CharacterGender;
use App\Enums\CharacterStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Character extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => CharacterStatus::class,
            'gender' => CharacterGender::class,
        ];
    }

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

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }
}
