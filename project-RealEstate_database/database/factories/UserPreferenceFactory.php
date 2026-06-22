<?php

namespace Database\Factories;

use App\Enums\InvestmentGoal;
use App\Models\Cities;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserPreference>
 */
class UserPreferenceFactory extends Factory
{
    protected $model = UserPreference::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'cities_id' => Cities::factory(),
            'places_id' => null,
            'min_budget' => 100_000,
            'max_budget' => 500_000,
            'preferred_property_type' => 'apartment',
            'preferred_rooms' => 3,
            'property_function' => null,
            'investment_goal' => InvestmentGoal::RentalIncome,
            'risk_level' => 'moderate',
            'interests' => null,
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn () => ['user_id' => $user->id]);
    }
}
