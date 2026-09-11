<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RawExternalPayload extends Model
{
    protected $fillable = [
        'entity_type',
        'external_id',
        'payload',
        'fetched_at'
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'fetched_at' => 'datetime',
        ];
    }
}
