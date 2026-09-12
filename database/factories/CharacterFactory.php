<?php

namespace Database\Factories;

use App\Enums\CharacterGender;
use App\Enums\CharacterStatus;
use App\Models\Character;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Character>
 */
class CharacterFactory extends Factory
{
    protected $model = Character::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'external_id' => fake()->unique()->numberBetween(1, 100000),
            'name' => fake()->name(),
            'status' => fake()->randomElement(CharacterStatus::cases())->value,
            'species' => 'Human',
            'type' => '',
            'gender' => fake()->randomElement(CharacterGender::cases())->value,
            'image' => 'https://rickandmortyapi.com/api/character/avatar/1.jpeg',
        ];
    }
}
