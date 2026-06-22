<?php

namespace App\Services;

use App\Enums\InteractionType;
use App\Models\Estate;
use App\Models\PropertyInteraction;
use App\Models\User;
use Illuminate\Support\Collection;

class PropertyInteractionService
{
    /**
     * Record a property interaction for the recommendation engine.
     * تسجيل حدث حصل من المستخدم.
     * المستخدم رقم 10 يشاهد عقار رقم 100
     * 
     */
    public function record(
        User $user,
        Estate $estate,
        InteractionType $type,
        ?int $score = null,
    ): PropertyInteraction {
        return PropertyInteraction::create([
            'user_id' => $user->id,
            'estate_id' => $estate->id,
            'interaction_type' => $type,
            'interaction_score' => $score ?? $type->defaultScore(),
        ]);
    }

    /**
     * Infer interests from behavioral history (views, favorites, shares, contacts).
     *
     * @return array{
     *     has_data: bool,
     *     total_interactions: int,
     *     total_weight: int,
     *     cities_id: ?int,
     *     places_id: ?int,
     *     avg_price: ?float,
     *     min_price: ?float,
     *     max_price: ?float,
     *     dominant_property_type: ?string,
     *     avg_bedrooms: ?float,
     *     top_estate_ids: list<int>,
     *     interaction_breakdown: array<string, int>
     * }|null
     */
    public function inferBehavioralProfile(User $user): ?array
    {
        $interactions = PropertyInteraction::query()
            ->where('user_id', $user->id)
            ->with(['estate.place'])
            ->whereHas('estate', fn ($q) => $q->where('status', 'active'))
            ->latest()
            ->limit(200)
            ->get();

        if ($interactions->isEmpty()) {
            return null;
        }

        $estateScores = [];//لتخزين العقارات التي يحبها المستخدم.
        $cityWeights = [];//لتخزين مدينة يحبها المستخدم.
        $placeWeights = [];//لتخزين منطقة يحبها المستخدم.
        $typeWeights = [];//لتخزين نوع العقار الذي يحبه المستخدم.
        $prices = [];//لتخزين الأسعار التي يحبها المستخدم.
        $bedrooms = [];//لتخزين عدد الغرف التي يحبها المستخدم.
        $breakdown = [];//لتخزين التفاعلات التي يحبها المستخدم.

        foreach ($interactions as $interaction) {
            $estate = $interaction->estate;
            if ($estate === null) {
                continue;
            }

            $weight = $interaction->interaction_score;
            $typeKey = $interaction->interaction_type->value;

            $breakdown[$typeKey] = ($breakdown[$typeKey] ?? 0) + 1;
            $estateScores[$estate->id] = ($estateScores[$estate->id] ?? 0) + $weight;

            if ($estate->place?->cities_id) {
                $cityId = $estate->place->cities_id;
                $cityWeights[$cityId] = ($cityWeights[$cityId] ?? 0) + $weight;
            }

            if ($estate->places_id) {
                $placeWeights[$estate->places_id] = ($placeWeights[$estate->places_id] ?? 0) + $weight;
            }
            //لتخزين نوع العقار الذي يحبه المستخدم.
            foreach ([$estate->type_text, $estate->kind_text] as $label) {
                if ($label) {
                    $key = strtolower($label);
                    $typeWeights[$key] = ($typeWeights[$key] ?? 0) + $weight;
                }
            }

            if ($estate->price > 0) {
                $prices[] = ['price' => (float) $estate->price, 'weight' => $weight];
            }

            if ($estate->num_of_bedrooms > 0) {
                $bedrooms[] = ['rooms' => $estate->num_of_bedrooms, 'weight' => $weight];
            }
        }
        //استخراج افضل العقارات التي يحبها المستخدم.
        arsort($estateScores);
        $topEstateIds = array_slice(array_keys($estateScores), 0, 10);

        $avgPrice = $this->weightedAverage($prices, 'price', 'weight');
        $avgBedrooms = $this->weightedAverage($bedrooms, 'rooms', 'weight');
    //حساب السعر الأدنى والأعلى.
        $minPrice = $avgPrice ? round($avgPrice * 0.85, 2) : null;
        $maxPrice = $avgPrice ? round($avgPrice * 1.15, 2) : null;
        //اعادة النتائج.
        return [
            'has_data' => true,
            'total_interactions' => $interactions->count(),
            'total_weight' => $interactions->sum('interaction_score'),
            'cities_id' => $this->topWeightedKey($cityWeights),
            'places_id' => $this->topWeightedKey($placeWeights),
            'avg_price' => $avgPrice,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'dominant_property_type' => $this->topWeightedKey($typeWeights),
            'avg_bedrooms' => $avgBedrooms !== null ? (int) round($avgBedrooms) : null,
            'top_estate_ids' => $topEstateIds,
            'interaction_breakdown' => $breakdown,
        ];
    }

    /**
     * @param  array<int, int>  $weights
     * هذه الدالة تأخذ المدينة الأكثر وزناً.
     */
    private function topWeightedKey(array $weights): ?int
    {
        if ($weights === []) {
            return null;
        }

        arsort($weights);

        return (int) array_key_first($weights);
    }

    /**
     * @param  list<array<string, float|int>>  $items
     * هذه لحساب المتوسط المرجح.
     * (400*1 +600*10) /11
    * 581.81
     */
    private function weightedAverage(array $items, string $valueKey, string $weightKey): ?float
    {
        if ($items === []) {
            return null;
        }

        $totalWeight = 0;
        $weightedSum = 0.0;

        foreach ($items as $item) {
            $weight = (int) $item[$weightKey];
            $totalWeight += $weight;
            $weightedSum += (float) $item[$valueKey] * $weight;
        }

        return $totalWeight > 0 ? round($weightedSum / $totalWeight, 2) : null;
    }
}
