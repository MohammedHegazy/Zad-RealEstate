<?php

namespace Database\Factories;

use App\Models\Companies;
use App\Models\Places;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Companies>
 */
class CompanyFactory extends Factory
{
    protected $model = Companies::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'places_id' => Places::factory(),
            'company_name' => fake()->company(),
            'website' => fake()->optional()->url(),
            'employees_num' => fake()->numberBetween(1, 500),
            'description' => fake()->paragraph(),
            'work_days' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'],
            'status' => 'approved',
            'profile_image' => null,
            'banner_image' => null,
            'trust_score' => 0,
        ];
    }

    public function forOwner(User $user): static
    {
        return $this->state(fn () => ['user_id' => $user->id]);
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => 'pending']);
    }

    public function approved(): static
    {
        return $this->state(fn () => ['status' => 'approved']);
    }

    public function rejected(): static
    {
        return $this->state(fn () => ['status' => 'rejected']);
    }

    public function suspended(): static
    {
        return $this->state(fn () => ['status' => 'suspended']);
    }
}
