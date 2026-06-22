<?php

namespace Database\Factories;

use App\Models\Cities;
use App\Models\Places;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Places>
 */
class PlaceFactory extends Factory
{
    protected $model = Places::class;

    public function definition(): array
    {
        return [
            'cities_id' => Cities::factory(),
            'name' => fake()->unique()->streetName(),
            'latitude' => fake()->latitude(33.4, 33.6),
            'longitude' => fake()->longitude(36.2, 36.4),
        ];
    }

    public function forCity(Cities $city): static
    {
        return $this->state(fn () => ['cities_id' => $city->id]);
    }
}
