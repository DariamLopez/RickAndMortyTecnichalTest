<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class CharacterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status->value,
            'species' => $this->species,
            'type' => $this->type,
            'gender' => $this->gender->value,
            'image' => $this->image,
        ];
    }
}
