<?php

namespace Database\Seeders;

use App\Models\Estate;
use App\Models\Places;
use App\Models\User;
use Illuminate\Database\Seeder;

class EstateSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::query()->where('email', 'owner@realestate.test')->first();

        if (! $owner) {
            $this->command?->warn('EstateSeeder skipped: demo owner not found.');

            return;
        }

        $malki = Places::query()->where('name', 'Malki')->first();
        $mazzeh = Places::query()->where('name', 'Mazzeh')->first();

        if (! $malki || ! $mazzeh) {
            $this->command?->warn('EstateSeeder skipped: seeded places not found.');

            return;
        }

        $estates = [
            [
                'name' => 'Sunlit Mazzeh Apartment',
                'places_id' => $mazzeh->id,
                'latitude' => 33.5200,
                'longitude' => 36.2800,
                'status' => 'active',
                'type_text' => 'residential',
                'kind_text' => 'apartment',
                'space_of_estate' => 145.00,
                'price_of_meter' => 3200.00,
                'price' => 464000.00,
                'monthly_rent' => 850.00,
                'num_of_bedrooms' => 3,
                'num_of_bathrooms' => 2,
                'description' => 'Bright apartment near services, suitable for families or rental income.',
            ],
            [
                'name' => 'Malki Investment Villa',
                'places_id' => $malki->id,
                'latitude' => 33.5140,
                'longitude' => 36.2770,
                'status' => 'active',
                'type_text' => 'residential',
                'kind_text' => 'villa',
                'space_of_estate' => 420.00,
                'price_of_meter' => 4100.00,
                'price' => 1722000.00,
                'monthly_rent' => 2800.00,
                'num_of_bedrooms' => 5,
                'num_of_bathrooms' => 4,
                'description' => 'Large villa with strong rental demand in a central district.',
            ],
            [
                'name' => 'Pending Abu Rummaneh Office',
                'places_id' => Places::query()->where('name', 'Abu Rummaneh')->value('id') ?? $malki->id,
                'latitude' => 33.5085,
                'longitude' => 36.2710,
                'status' => 'pending',
                'type_text' => 'commercial',
                'kind_text' => 'office',
                'space_of_estate' => 95.00,
                'price_of_meter' => 3800.00,
                'price' => 361000.00,
                'monthly_rent' => null,
                'num_of_bedrooms' => 0,
                'num_of_bathrooms' => 1,
                'description' => 'Commercial office awaiting admin approval.',
            ],
        ];

        foreach ($estates as $payload) {
            $price = (float) $payload['price'];
            $monthlyRent = $payload['monthly_rent'];
            $annualIncome = $monthlyRent !== null ? round($monthlyRent * 12 * 0.95, 2) : null;
            $roi = $annualIncome !== null && $price > 0
                ? round($annualIncome / $price, 4)
                : null;
            $payback = $roi !== null && $roi > 0
                ? round(1 / $roi, 2)
                : null;

            $estate = Estate::query()->updateOrCreate(
                [
                    'user_id' => $owner->id,
                    'name' => $payload['name'],
                ],
                array_merge($this->baseEstateAttributes($owner->id), $payload, [
                    'expected_annual_income' => $annualIncome,
                    'roi' => $roi,
                    'payback_period' => $payback,
                ]),
            );

            $estate->socialLinks()->updateOrCreate(
                ['platform' => 'whatsapp'],
                ['url' => 'https://wa.me/963'.fake()->numerify('#########')],
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function baseEstateAttributes(int $userId): array
    {
        return [
            'user_id' => $userId,
            'phone' => '944123456',
            'country_code_phone' => '+963',
            'annual_expenses' => 1200,
            'maintenance_cost' => 800,
            'annual_property_tax' => 500,
            'annual_hoa_or_service' => 300,
            'occupancy_rate' => 95,
            'floor' => 2,
            'num_of_livingrooms' => 1,
            'num_of_receptions' => 1,
            'num_of_kitchens' => 1,
            'num_of_balconies' => 1,
            'is_furnished' => false,
            'real_number' => null,
            'date_of_build' => '2018',
            'state_of_build' => 'good',
            'rent_kind' => null,
            'rent_description' => null,
            'views' => fake()->numberBetween(5, 120),
            'shares' => fake()->numberBetween(0, 15),
        ];
    }
}
