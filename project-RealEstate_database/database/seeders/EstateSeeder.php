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
        $owners = User::query()->whereIn('type', ['owner', 'company', 'agent'])->orderBy('id')->get();
        $places = Places::query()->with('city')->orderBy('cities_id')->orderBy('id')->get();

        if ($owners->isEmpty() || $places->isEmpty()) {
            $this->command?->warn('EstateSeeder skipped: no owners or places found.');
            return;
        }

        $ownerCount = $owners->count();

        $namePrefixes = [
            'residential' => ['شقة', 'فيلا', 'دوبلكس', 'تاون هاوس', 'استوديو'],
            'commercial' => ['مكتب', 'متجر', 'مستودع', 'عمارة', 'مركز تجاري'],
        ];

        $kindByType = [
            'residential' => ['apartment', 'villa', 'duplex', 'townhouse', 'studio'],
            'commercial' => ['office', 'shop', 'warehouse', 'building', 'mall'],
        ];

        $statuses = ['active', 'active', 'active', 'active', 'pending', 'sold', 'rented'];

        $index = 0;

        foreach ($places as $place) {
            $estateCount = fake()->numberBetween(2, 3);

            for ($e = 0; $e < $estateCount; $e++) {
                $owner = $owners->get($index % $ownerCount);

                $typeText = fake()->randomElement(['residential', 'commercial', 'residential', 'residential']);
                $kindIndex = fake()->numberBetween(0, 4);
                $kindText = $kindByType[$typeText][$kindIndex];
                $namePrefix = $namePrefixes[$typeText][$kindIndex];

                $space = fake()->randomFloat(2, $typeText === 'residential' ? 60 : 30, $typeText === 'residential' ? 500 : 300);
                $pricePerMeter = fake()->randomFloat(2, 200, ($typeText === 'residential' ? 4000 : 3000));
                $price = round($space * $pricePerMeter, 2);
                $monthlyRent = $kindText === 'warehouse' || $kindText === 'land'
                    ? null
                    : fake()->optional($typeText === 'residential' ? 0.6 : 0.5)->randomFloat(2, 150, 5000);
                $annualIncome = $monthlyRent !== null ? round($monthlyRent * 12 * 0.95, 2) : null;
                $roi = $annualIncome !== null && $price > 0 ? round($annualIncome / $price, 4) : null;
                $payback = $roi !== null && $roi > 0 ? round(1 / $roi, 2) : null;

                $latOffset = fake()->randomFloat(6, -0.01, 0.01);
                $lngOffset = fake()->randomFloat(6, -0.01, 0.01);

                $estateName = $namePrefix . ' ' . $place->name . ' ' . ($e + 1);

                $status = fake()->randomElement($statuses);

                Estate::query()->updateOrCreate(
                    [
                        'user_id' => $owner->id,
                        'name' => $estateName,
                        'places_id' => $place->id,
                    ],
                    [
                        'places_id' => $place->id,
                        'latitude' => $place->latitude + $latOffset,
                        'longitude' => $place->longitude + $lngOffset,
                        'phone' => fake()->numerify('94########'),
                        'country_code_phone' => '+963',
                        'space_of_estate' => $space,
                        'price_of_meter' => $pricePerMeter,
                        'price' => $price,
                        'monthly_rent' => $monthlyRent,
                        'annual_expenses' => fake()->randomFloat(2, 100, 2000),
                        'maintenance_cost' => fake()->randomFloat(2, 200, 1500),
                        'annual_property_tax' => fake()->randomFloat(2, 100, 1000),
                        'annual_hoa_or_service' => fake()->randomFloat(2, 100, 600),
                        'occupancy_rate' => $status === 'rented' ? 100 : fake()->randomFloat(2, 70, 100),
                        'expected_annual_income' => $annualIncome,
                        'roi' => $roi,
                        'payback_period' => $payback,
                        'floor' => $typeText === 'residential' ? fake()->numberBetween(1, 12) : fake()->numberBetween(1, 5),
                        'num_of_bedrooms' => $typeText === 'residential' ? fake()->numberBetween(1, 6) : 0,
                        'num_of_livingrooms' => $typeText === 'residential' ? fake()->numberBetween(1, 2) : 0,
                        'num_of_receptions' => $typeText === 'residential' ? fake()->numberBetween(1, 2) : 1,
                        'num_of_bathrooms' => fake()->numberBetween(1, 4),
                        'num_of_kitchens' => $typeText === 'residential' ? fake()->numberBetween(1, 2) : 1,
                        'num_of_balconies' => $typeText === 'residential' ? fake()->numberBetween(0, 3) : 0,
                        'status' => $status,
                        'type_text' => $typeText,
                        'kind_text' => $kindText,
                        'is_furnished' => $typeText === 'residential' && fake()->boolean(30),
                        'description' => $this->generateDescription($typeText, $kindText, $place->city->name, $place->name),
                        'date_of_build' => (string) fake()->numberBetween(1995, 2024),
                        'state_of_build' => fake()->randomElement(['excellent', 'good', 'fair', 'needs_renovation']),
                        'rent_kind' => $monthlyRent !== null ? fake()->randomElement(['monthly', 'yearly', 'long_term']) : null,
                        'rent_description' => $monthlyRent !== null ? 'عقد إيجار لمدة ' . fake()->numberBetween(1, 5) . ' سنوات' : null,
                        'views' => fake()->numberBetween(0, 500),
                        'shares' => fake()->numberBetween(0, 50),
                    ],
                );

                $index++;
            }
        }
    }

    private function generateDescription(string $type, string $kind, string $city, string $place): string
    {
        $templates = [
            $type === 'residential'
                ? "{$kind} مميزة في {$place} بدمشق، مساحة واسعة مع إطلالة رائعة. قريبة من جميع الخدمات والمواصلات."
                : "{$kind} تجاري في {$place} بدمشق، موقع استراتيجي مناسب للمشاريع التجارية والاستثمار.",
            "عقار {$type} في {$place} - {$city}، تشطيب حديث مع خدمة المصعد. يتميز بقربه من المدارس والأسواق.",
            "فرصة استثمارية ممتازة: {$kind} {$type} في {$place} {$city}، مناسب للسكن أو الاستثمار.",
        ];
        return fake()->randomElement($templates);
    }
}
