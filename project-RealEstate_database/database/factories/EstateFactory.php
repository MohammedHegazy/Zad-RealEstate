<?php

namespace Database\Factories;

use App\Models\Estate;
use App\Models\Places;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Estate>
 */
class EstateFactory extends Factory
{
    protected $model = Estate::class;

    public function definition(): array
    {
        $space = fake()->randomFloat(2, 80, 500);
        $pricePerMeter = fake()->randomFloat(2, 500, 5000);
        $price = round($space * $pricePerMeter, 2);
        $monthlyRent = fake()->optional(0.7)->randomFloat(2, 1000, 15000);
        $annualIncome = $monthlyRent !== null ? round($monthlyRent * 12 * 0.95, 2) : null;
        $roi = $annualIncome !== null && $price > 0 ? round($annualIncome / $price, 4) : null;

        return [
            'user_id' => User::factory(),
            'places_id' => Places::factory(),
            'latitude' => fake()->latitude(33.4, 33.6),
            'longitude' => fake()->longitude(36.2, 36.4),
            'name' => fake()->words(3, true),
            'phone' => fake()->numerify('##########'),
            'country_code_phone' => '+963',
            'space_of_estate' => $space,
            'price_of_meter' => $pricePerMeter,
            'price' => $price,
            'monthly_rent' => $monthlyRent,
            'annual_expenses' => 0,
            'maintenance_cost' => 0,
            'annual_property_tax' => 0,
            'annual_hoa_or_service' => 0,
            'occupancy_rate' => 100,
            'expected_annual_income' => $annualIncome,
            'roi' => $roi,
            'payback_period' => $roi !== null && $roi > 0 ? round(1 / $roi, 2) : null,
            'floor' => fake()->numberBetween(0, 20),
            'num_of_bedrooms' => fake()->numberBetween(1, 6),
            'num_of_livingrooms' => 1,
            'num_of_receptions' => 1,
            'num_of_bathrooms' => fake()->numberBetween(1, 4),
            'num_of_kitchens' => 1,
            'num_of_balconies' => fake()->numberBetween(0, 3),
            'status' => 'active',
            'type_text' => 'residential',
            'kind_text' => 'apartment',
            'is_furnished' => false,
            'description' => fake()->paragraph(),
            'real_number' => null,
            'date_of_build' => (string) fake()->numberBetween(1995, 2024),
            'state_of_build' => 'good',
            'rent_kind' => null,
            'rent_description' => null,
            'views' => 0,
            'shares' => 0,
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

    public function active(): static
    {
        return $this->state(fn () => ['status' => 'active']);
    }

    public function withCoordinates(?float $latitude = null, ?float $longitude = null): static
    {
        return $this->state(fn () => [
            'latitude' => $latitude ?? fake()->latitude(33.4, 33.6),
            'longitude' => $longitude ?? fake()->longitude(36.2, 36.4),
        ]);
    }
}
