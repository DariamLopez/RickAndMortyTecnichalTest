<?php

namespace Database\Factories;

use App\Models\Episode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Episode>
 */
class EpisodeFactory extends Factory
{
    protected $model = Episode::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
         return [
            'external_id' => fake()->unique()->numberBetween(1, 100000),
            'name' => fake()->sentence(3),
            'air_date' => fake()->date(),
            'episode_code' => 'S01E01',
        ];
    }
}
