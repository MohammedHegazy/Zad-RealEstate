<?php

namespace Database\Factories;

use App\Models\InvestmentPortfolio;
use App\Models\Estate;
use App\Models\Portfolio;
use App\Models\PortfolioItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PortfolioItem>
 */
class PortfolioItemFactory extends Factory
{
    protected $model = PortfolioItem::class;

    public function definition(): array
    {
        return [
            'portfolio_id' => InvestmentPortfolio::factory(),
            'estate_id' => function () {
                $estateId = Estate::query()->value('id');

                if ($estateId === null) {
                    throw new \RuntimeException(
                        'Create an Estate (or pass forEstate()) before using PortfolioItemFactory.'
                    );
                }

                return $estateId;
            },
            'status' => fake()->randomElement(PortfolioItem::statuses()),
            'investment_amount' => fake()->optional(0.7)->randomFloat(2, 50_000, 2_000_000),
            'notes' => fake()->optional(0.5)->paragraph(),
            'invested_at' => null,
            'sold_at' => null,
        ];
    }

    public function tracking(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PortfolioItem::STATUS_TRACKING,
            'investment_amount' => null,
            'invested_at' => null,
            'sold_at' => null,
        ]);
    }

    public function invested(?float $amount = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PortfolioItem::STATUS_INVESTED,
            'investment_amount' => $amount ?? fake()->randomFloat(2, 50_000, 2_000_000),
            'invested_at' => now(),
            'sold_at' => null,
        ]);
    }

    public function sold(?float $amount = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PortfolioItem::STATUS_SOLD,
            'investment_amount' => $amount ?? fake()->randomFloat(2, 50_000, 2_000_000),
            'invested_at' => now()->subMonths(fake()->numberBetween(6, 36)),
            'sold_at' => now(),
        ]);
    }

    public function forPortfolio(Portfolio|InvestmentPortfolio $portfolio): static
    {
        return $this->state(fn (array $attributes) => [
            'portfolio_id' => $portfolio->id,
        ]);
    }

    public function forEstate(Estate $estate): static
    {
        return $this->state(fn (array $attributes) => [
            'estate_id' => $estate->id,
        ]);
    }
}
