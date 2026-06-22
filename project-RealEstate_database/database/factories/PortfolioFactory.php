<?php

namespace Database\Factories;

use App\Models\InvestmentPortfolio;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvestmentPortfolio>
 */
class PortfolioFactory extends Factory
{
    protected $model = InvestmentPortfolio::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->randomElement(['My Portfolio', 'Rental Holdings', 'Watchlist', 'Long-term Investments']),
            'description' => fake()->optional(0.6)->sentence(),
            'target_budget' => fake()->optional(0.5)->randomFloat(2, 100_000, 5_000_000),
            'risk_level' => fake()->randomElement(InvestmentPortfolio::riskLevels()),
            'status' => InvestmentPortfolio::STATUS_ACTIVE,
            'is_default' => false,
        ];
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'My Portfolio',
            'is_default' => true,
        ]);
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}
