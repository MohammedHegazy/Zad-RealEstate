<?php

namespace Database\Factories;

use App\Models\Companies;
use App\Models\CompanyReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CompanyReview>
 */
class CompanyReviewFactory extends Factory
{
    protected $model = CompanyReview::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'company_id' => Companies::factory(),
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
