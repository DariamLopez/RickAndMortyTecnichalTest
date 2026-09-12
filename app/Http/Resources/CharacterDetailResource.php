<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CharacterDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
            'species' => $this->species,
            'type' => $this->type,
            'gender' => $this->gender,
            'image' => $this->image,
            'origin' => $this->whenLoaded('origin', fn () => $this->origin ? new LocationResource($this->origin) : null),
            'current_location' => $this->whenLoaded('current', fn () => $this->current ? new LocationResource($this->current) : null),
            'episodes' => EpisodeResource::collection($this->whenLoaded('episodes')),
        ];
    }
}
