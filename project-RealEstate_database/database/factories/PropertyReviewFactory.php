<?php

namespace Database\Factories;

use App\Models\Estate;
use App\Models\PropertyReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyReview>
 */
class PropertyReviewFactory extends Factory
{
    protected $model = PropertyReview::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'estate_id' => Estate::factory(),
            'rating' => fake()->numberBetween(1, 5),
            'review' => fake()->paragraph(),
            'status' => 'pending',
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => ['status' => 'approved']);
    }
}
