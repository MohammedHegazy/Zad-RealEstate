<?php

namespace App\Services;

use App\Models\Estate; //لجلب العقار المرشح 
use App\Models\Recommendation;//لانشاء وحفظ التوصيات 
use App\Models\User;//المستخدم الذي سنولد له التوصيات 
use App\Models\UserPreference;//تفضيلات المستخدم 
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class RecommendationGeneratorService
{
    public function __construct(
        private readonly PropertyInteractionService $interactions,// فهم سلوك المستخدم 
        private readonly RecommendationScoringService $scoring,// ممحرك الحساب هذا العقار مناسب بنسبة 80
    ) {}

    /**
     * @return array{generated: int, deactivated: int}
     */
    public function generateForUser(User $user): array
    {
        $favorites = $this->loadActiveFavoriteEstates($user);
        $preference = $user->preference;
        $behavior = $this->interactions->inferBehavioralProfile($user);
        //فحص هل يوجد معلومات كافية 
        if ($favorites->isEmpty() && $preference === null && $behavior === null) {
            $deactivated = Recommendation::query()
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            return ['generated' => 0, 'deactivated' => $deactivated];
        }
        //اخيتار طريقة التوليد 
        if ($favorites->isNotEmpty()) {
            $scored = $this->scoreCandidatesAgainstFavorites($favorites, $preference, $behavior);
        } else {
            $candidates = $this->fetchCandidates($preference, $behavior);
            $scored = $this->scoreCandidates($candidates, $preference, $behavior); //يحسب الدرجة 
        }
        //ترتيب النتائج  يعني ترتيب تنازلي 
        $scored = $scored->sortByDesc('score')->values();

        $limit = (int) config('realestate.recommendation_limit', 30);
        $minScore = (float) config('realestate.recommendation_min_score', 40);
        // تعطيل التوصية القديمة 
        $deactivated = Recommendation::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        $generated = 0;
        //تاخذ التوصيات الجديدة 
        foreach ($scored->take($limit) as $item) {
            if ($item['score'] < $minScore) {
                continue;
            }

            Recommendation::updateOrCreate( 
                [
                    'user_id' => $user->id,
                    'estate_id' => $item['estate']->id,
                ],
                [
                    'recommendation_score' => $item['score'],
                    'matching_percentage' => $item['matching_percentage'],
                    'score_factors' => $item['factors'],
                    'recommendation_reason' => $item['reasons'],
                    'is_active' => true,
                ]
            );

            $generated++;
        }

        return ['generated' => $generated, 'deactivated' => $deactivated];
    }

    /**
     * @return Collection<int, Estate>
     */
    public function loadActiveFavoriteEstates(User $user): Collection
    {
        $favoriteIds = $user->favoriteEstates()->pluck('estate_id');

        if ($favoriteIds->isEmpty()) { 
            return collect();
        }

        return Estate::query()
            ->where('status', 'active')
            ->whereIn('id', $favoriteIds)
            ->with(['place.city'])
            ->get();
    }

    /**
     * @return Collection<int, array{
     *     estate: Estate,
     *     score: float,
     *     matching_percentage: float,
     *     factors: array<string, float>,
     *     reasons: list<string>
     * }>
     */
    private function scoreCandidatesAgainstFavorites(
        Collection $favorites,
        ?UserPreference $preference,
        ?array $behavior,
    ): Collection {
        $favoriteIds = $favorites->pluck('id')->all();
        $poolSize = (int) config('realestate.recommendation_candidate_pool', 150);

        $candidates = Estate::query()
            ->where('status', 'active')
            ->whereNotIn('id', $favoriteIds)
            ->with(['place.city'])
            ->latest('id')
            ->limit($poolSize)
            ->get();

        return $candidates->map(function (Estate $estate) use ($favorites, $preference, $behavior) {
            $favoriteResult = $this->scoring->scoreAgainstFavorites($estate, $favorites);
            $score = $favoriteResult['score'];
            $matchingPercentage = $favoriteResult['matching_percentage'];
            $factors = $favoriteResult['factors'];
            $reasons = $favoriteResult['reasons'];

            if ($preference !== null) {
                $preferenceResult = $this->scoring->scoreEstate($estate, $preference, $behavior);
                $score = round(($favoriteResult['score'] * 0.75) + ($preferenceResult['score'] * 0.25), 2);//دمج المفضلات و التفضيلات 
                $factors['preference_match'] = $preferenceResult['matching_percentage'];
                $reasons = array_values(array_unique(array_merge($reasons, $preferenceResult['reasons'])));
            }

            return [
                'estate' => $estate,
                'score' => $score,
                'matching_percentage' => $matchingPercentage,
                'factors' => $factors,
                'reasons' => $reasons,
            ];
        });
    }

    /**
     * @return Collection<int, Estate>
     */
    private function fetchCandidates(?UserPreference $preference, ?array $behavior): Collection
    {
        $poolSize = (int) config('realestate.recommendation_candidate_pool', 150);

        $query = Estate::query()
            ->where('status', 'active')
            ->with(['place.city']);

        if ($behavior !== null && $behavior['top_estate_ids'] !== []) {
            $query->whereNotIn('id', $behavior['top_estate_ids']);
        }

        $cityId = $preference?->cities_id ?? $behavior['cities_id'] ?? null;
        $placeId = $preference?->places_id ?? $behavior['places_id'] ?? null;

        if ($placeId) {
            $query->where('places_id', $placeId);
        } elseif ($cityId) {
            $query->whereHas('place', fn (Builder $q) => $q->where('cities_id', $cityId));
        }

        return $query->latest('id')->limit($poolSize)->get();
    }

    /**
     * @return Collection<int, array{
     *     estate: Estate,
     *     score: float,
     *     matching_percentage: float,
     *     factors: array<string, float>,
     *     reasons: list<string>
     * }>
     */
    private function scoreCandidates(
        Collection $candidates,
        ?UserPreference $preference,
        ?array $behavior,
    ): Collection {
        return $candidates->map(function (Estate $estate) use ($preference, $behavior) {
            $result = $this->scoring->scoreEstate($estate, $preference, $behavior);

            return [
                'estate' => $estate,
                'score' => $result['score'],
                'matching_percentage' => $result['matching_percentage'],
                'factors' => $result['factors'],
                'reasons' => $result['reasons'],
            ];
        });
    }
}
