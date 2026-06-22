<?php

namespace Database\Factories;

use App\Models\Agent;
use App\Models\AgentReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AgentReview>
 */
class AgentReviewFactory extends Factory
{
    protected $model = AgentReview::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'agent_id' => Agent::factory(),
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
