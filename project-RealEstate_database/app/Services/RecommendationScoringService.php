<?php

namespace App\Services;

use App\Enums\InvestmentGoal;
use App\Models\Estate;
use App\Models\UserPreference;
use Illuminate\Support\Collection;

/**
 * Weighted scoring engine for property recommendations (0–100).
 */
class RecommendationScoringService
{
    /** @var array<string, float> */
    private const WEIGHTS = [
        'budget_match' => 0.25,
        'location_match' => 0.25,
        'property_type_match' => 0.20,
        'roi_potential' => 0.15,
        'investment_goal_match' => 0.15,
    ];

    /**
     * @return array{
     *     score: float,
     *     matching_percentage: float,
     *     factors: array<string, float>,
     *     reasons: list<string>
     * }
     */
    public function scoreEstate(
        Estate $estate,
        ?UserPreference $preference,
        ?array $behavior = null,
    ): array {
        $factors = [
            'budget_match' => $this->scoreBudget($estate, $preference, $behavior),
            'location_match' => $this->scoreLocation($estate, $preference, $behavior),
            'property_type_match' => $this->scorePropertyType($estate, $preference, $behavior),
            'roi_potential' => $this->scoreRoi($estate, $preference),
            'investment_goal_match' => $this->scoreInvestmentGoal($estate, $preference),
        ];

        $matchingPercentage = 0.0;

        foreach (self::WEIGHTS as $key => $weight) {
            $matchingPercentage += $factors[$key] * $weight;
        }

        $behaviorBonus = $this->behaviorBonus($estate, $behavior);
        $score = min(100, round($matchingPercentage + $behaviorBonus, 2));
        $matchingPercentage = round($matchingPercentage, 2);

        return [
            'score' => $score,
            'matching_percentage' => $matchingPercentage,
            'factors' => array_map(fn (float $v) => round($v, 2), $factors),
            'reasons' => $this->buildReasons($estate, $factors, $preference, $behavior),
        ];
    }

    /**
     * Score a candidate against the user's favorite estates (0–100).
     *
     * @param  Collection<int, Estate>  $favorites
     * @return array{
     *     score: float,
     *     matching_percentage: float,
     *     factors: array<string, float>,
     *     reasons: list<string>,
     *     best_match_estate_id: ?int,
     *     best_similarity: float
     * }
     */
    public function scoreAgainstFavorites(Estate $candidate, Collection $favorites): array
    {
        // هل قائمة المفضلة فارغة ام لا 
        // اذا فارغة يرجع 0 يعني يقول ما اقدر اقارن لان مافي مفضلة اصلاً
        if ($favorites->isEmpty()) {
            return [
                'score' => 0,
                'matching_percentage' => 0,
                'factors' => [],
                'reasons' => [],
                'best_match_estate_id' => null,
                'best_similarity' => 0,
            ];
        }

        $similarities = $favorites->map(
            fn (Estate $favorite) => [
                'estate' => $favorite,
                'similarity' => $this->scoreSimilarity($favorite, $candidate),
            ]
        );

        $best = $similarities->sortByDesc('similarity')->first();
        $bestScore = (float) ($best['similarity'] ?? 0);
        $avgScore = round((float) $similarities->avg('similarity'), 2);
        $score = round(($bestScore * 0.7) + ($avgScore * 0.3), 2);

        /** @var Estate|null $bestFavorite */
        $bestFavorite = $best['estate'] ?? null;

        return [
            'score' => $score,
            'matching_percentage' => $score,
            'factors' => [
                'favorite_similarity' => $score,
                'best_match_similarity' => $bestScore,
                'average_favorite_similarity' => $avgScore,
            ],
            'reasons' => $this->buildFavoriteReasons($candidate, $bestFavorite, $bestScore),
            'best_match_estate_id' => $bestFavorite?->id,
            'best_similarity' => $bestScore,
        ];
    }

    /**
     * Score similarity between two estates (0–100) for "similar properties" API.
     */
    public function scoreSimilarity(Estate $source, Estate $candidate): float
    {
        $factors = [];

        $sourcePrice = (float) $source->price;
        $candidatePrice = (float) $candidate->price;

        if ($sourcePrice > 0 && $candidatePrice > 0) {
            $ratio = min($sourcePrice, $candidatePrice) / max($sourcePrice, $candidatePrice);
            $factors[] = $ratio * 100;
        }

        if ($source->places_id && $source->places_id === $candidate->places_id) {
            $factors[] = 100;
        } elseif ($source->place?->cities_id && $source->place->cities_id === $candidate->place?->cities_id) {
            $factors[] = 70;
        } else {
            $factors[] = 0;
        }

        $factors[] = $this->matchesPropertyType($candidate, (string) $source->type_text) ? 100 : 0;
        $factors[] = $this->matchesPropertyType($candidate, (string) $source->kind_text) ? 80 : 0;

        if ($source->num_of_bedrooms > 0 && $candidate->num_of_bedrooms > 0) {
            $bedDiff = abs($source->num_of_bedrooms - $candidate->num_of_bedrooms);
            $factors[] = max(0, 100 - ($bedDiff * 25));
        }

        return round(array_sum($factors) / count($factors), 2);
    }

    private function scoreBudget(Estate $estate, ?UserPreference $preference, ?array $behavior): float
    {
        $min = $preference?->min_budget ?? $behavior['min_price'] ?? null;
        $max = $preference?->max_budget ?? $behavior['max_price'] ?? null;
        $price = (float) $estate->price;

        if ($price <= 0) {
            return 0;
        }

        if ($min !== null && $max !== null) {
            if ($price >= $min && $price <= $max) {
                return 100;
            }

            if ($price <= $max * 1.1 && $price >= $min * 0.9) {
                return 70;
            }

            return 20;
        }

        if ($max !== null) {
            return $price <= $max ? 85 : max(0, 100 - (($price - $max) / $max * 100));
        }

        return 50;
    }

    private function scoreLocation(Estate $estate, ?UserPreference $preference, ?array $behavior): float
    {
        $preferredPlace = $preference?->places_id ?? $behavior['places_id'] ?? null;
        $preferredCity = $preference?->cities_id ?? $behavior['cities_id'] ?? null;

        if ($preferredPlace && $estate->places_id === $preferredPlace) {
            return 100;
        }

        if ($preferredCity && $estate->place?->cities_id === $preferredCity) {
            return 80;
        }

        if ($preferredCity || $preferredPlace) {
            return 0;
        }

        return 50;
    }

    private function scorePropertyType(Estate $estate, ?UserPreference $preference, ?array $behavior): float
    {
        $preferredType = $preference?->preferred_property_type
            ?? $behavior['dominant_property_type']
            ?? null;

        if ($preferredType === null) {
            return 50;
        }

        return $this->matchesPropertyType($estate, $preferredType) ? 100 : 15;
    }

    private function scoreRoi(Estate $estate, ?UserPreference $preference): float
    {
        $roi = (float) ($estate->roi ?? 0);
        $risk = $preference?->risk_level ?? 'moderate';

        if ($roi <= 0) {
            return $estate->monthly_rent ? 40 : 20;
        }

        $normalized = min(100, $roi * 8);

        return match ($risk) {
            'low' => min($normalized, 60),
            'high' => min(100, $normalized * 1.2),
            default => $normalized,
        };
    }

    private function scoreInvestmentGoal(Estate $estate, ?UserPreference $preference): float
    {
        $goal = $preference?->investment_goal;

        if ($goal === null) {
            return 50;
        }

        return match ($goal) {
            InvestmentGoal::RentalIncome => min(100, max(20, (float) ($estate->roi ?? 0) * 10 + ($estate->monthly_rent ? 20 : 0))),
            InvestmentGoal::CapitalGrowth => ($estate->price > 0 && $estate->place) ? 80 : 40,
            InvestmentGoal::PrimaryHome => $estate->num_of_bedrooms >= 2 ? 90 : 55,
            InvestmentGoal::Flip => $this->scoreFlipPotential($estate),
            InvestmentGoal::CommercialUse => $this->matchesPropertyType($estate, 'commercial') ? 95 : 25,
        };
    }

    private function scoreFlipPotential(Estate $estate): float
    {
        $pricePerMeter = (float) ($estate->price_of_meter ?? 0);
        $space = (float) ($estate->space_of_estate ?? 0);

        if ($pricePerMeter <= 0 || $space <= 0) {
            return 45;
        }

        return min(100, max(30, 100 - ($pricePerMeter / 100)));
    }

    private function behaviorBonus(Estate $estate, ?array $behavior): float
    {
        if ($behavior === null || $behavior['total_interactions'] === 0) {
            return 0;
        }

        $bonus = 0.0;

        if ($behavior['dominant_property_type'] && $this->matchesPropertyType($estate, $behavior['dominant_property_type'])) {
            $bonus += 5;
        }

        if ($behavior['places_id'] && $estate->places_id === $behavior['places_id']) {
            $bonus += 3;
        }

        return $bonus;
    }

    /**
     * @param  array<string, float>  $factors
     * @return list<string>
     */
    private function buildReasons(
        Estate $estate,
        array $factors,
        ?UserPreference $preference,
        ?array $behavior,
    ): array {
        $reasons = [];

        if ($factors['budget_match'] >= 70) {
            $reasons[] = 'Within your budget range';
        }

        if ($factors['location_match'] >= 80) {
            $reasons[] = $preference?->places_id
                ? 'Located in your preferred area'
                : 'Located in your preferred city';
        } elseif ($factors['location_match'] >= 50 && $behavior) {
            $reasons[] = 'In a location you frequently explore';
        }

        if ($factors['property_type_match'] >= 80) {
            $reasons[] = 'Matches your preferred property type';
        }

        if ($factors['roi_potential'] >= 60) {
            $reasons[] = 'Strong ROI potential for your risk profile';
        }

        if ($factors['investment_goal_match'] >= 70 && $preference?->investment_goal) {
            $reasons[] = 'Aligns with your investment goal: '.str_replace('_', ' ', $preference->investment_goal->value);
        }

        if ($behavior !== null && $behavior['total_interactions'] > 0) {
            $reasons[] = 'Similar to properties you have interacted with';
        }

        if ($reasons === []) {
            $reasons[] = 'Recommended based on your profile';
        }

        return array_values(array_unique($reasons));
    }

    private function buildFavoriteReasons(Estate $candidate, ?Estate $bestFavorite, float $score): array
    {
        if ($bestFavorite === null) {
            return ['مشابه لعقارات في قائمة المفضلة'];
        }

        $reasons = [];

        if ($bestFavorite->places_id && $bestFavorite->places_id === $candidate->places_id) {
            $reasons[] = 'نفس المنطقة مع أحد عقاراتك المفضلة';
        } elseif (
            $bestFavorite->place?->cities_id
            && $bestFavorite->place->cities_id === $candidate->place?->cities_id
        ) {
            $reasons[] = 'نفس المدينة مع أحد عقاراتك المفضلة';
        }

        if ($this->matchesPropertyType($candidate, (string) $bestFavorite->type_text)
            || $this->matchesPropertyType($candidate, (string) $bestFavorite->kind_text)) {
            $reasons[] = 'نوع عقار مشابه لمفضلتك';
        }

        $favoritePrice = (float) $bestFavorite->price;
        $candidatePrice = (float) $candidate->price;

        if ($favoritePrice > 0 && $candidatePrice > 0) {
            $ratio = min($favoritePrice, $candidatePrice) / max($favoritePrice, $candidatePrice);
            if ($ratio >= 0.85) {
                $reasons[] = 'قريب من نطاق أسعار مفضلتك';
            }
        }

        if ($bestFavorite->num_of_bedrooms > 0 && $candidate->num_of_bedrooms > 0) {
            $bedDiff = abs($bestFavorite->num_of_bedrooms - $candidate->num_of_bedrooms);
            if ($bedDiff <= 1) {
                $reasons[] = 'عدد غرف قريب من مفضلتك';
            }
        }

        if ($score >= 70) {
            $reasons[] = 'شبيه جداً بـ «'.$bestFavorite->name.'» في مفضلتك';
        } elseif ($reasons === []) {
            $reasons[] = 'مقارب لـ «'.$bestFavorite->name.'» في مفضلتك';
        }

        return array_values(array_unique($reasons));
    }

    private function matchesPropertyType(Estate $estate, ?string $type): bool
    {
        if ($type === null || $type === '') {
            return false;
        }

        $needle = strtolower($type);

        return str_contains(strtolower((string) $estate->type_text), $needle)
            || str_contains(strtolower((string) $estate->kind_text), $needle);
    }
}
