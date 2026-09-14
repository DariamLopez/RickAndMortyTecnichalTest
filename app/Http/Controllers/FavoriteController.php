<?php

namespace App\Http\Controllers;

use App\Http\Resources\CharacterResource;
use App\Models\Character;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FavoriteController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $favorites = $request->user()->favoriteCharacters()->orderBy('name')->paginate(20);

        return CharacterResource::collection($favorites);
    }

    public function store(Request $request, Character $character): JsonResponse
    {
        $request->user()->favoriteCharacters()->syncWithoutDetaching([$character->id]);

        return response()->json([
            'data' => new CharacterResource($character)
        ], 201);
    }

    public function destroy(Request $request, Character $character): JsonResponse
    {
        $character = $request->user()->favoriteCharacters()->find($character->id);
        if (!$character) {
            return response()->json(['message' => "Removed (or wasn't a favorite)."], 204);
        }
        $request->user()->favoriteCharacters()->detach($character->id);

        return response()->json(status: 204);
    }
}
