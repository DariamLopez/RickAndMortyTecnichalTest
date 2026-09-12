<?php

namespace App\Http\Controllers;

use App\Http\Requests\Character\CharacterIndexRequest;
use App\Http\Resources\CharacterDetailResource;
use App\Http\Resources\CharacterResource;
use App\Models\Character;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CharacterController
{
    public function index(CharacterIndexRequest $request): AnonymousResourceCollection
    {
        $characters = Character::query()
            ->when($request->filled('name'), fn($query) => $query->where('name', 'like', '%' . $request->string('name') . '%'))
            ->when($request->filled('status'), fn($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('species'), fn($query) => $query->where('species', $request->string('species')))
            ->when($request->filled('gender'), fn($query) => $query->where('gender', $request->string('gender')))
            ->orderBy('name')
            ->paginate($request->integer('per_page', 20));

        return CharacterResource::collection($characters);
    }

    public function show(int $id): CharacterDetailResource | JsonResponse
    {
        $character = Character::find($id);

        if(!$character){
            return response()->json(['message' => 'Resource not found.'], 404);
        }
        $character->load(['origin', 'current', 'episodes']);

        return new CharacterDetailResource($character);
    }
}
