<?php

namespace Database\Factories;

use App\Models\Cities;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cities>
 */
class CityFactory extends Factory
{
    protected $model = Cities::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->city(),
            'image' => null,
            'latitude' => fake()->latitude(33.4, 33.6),
            'longitude' => fake()->longitude(36.2, 36.4),
        ];
    }
}
