<?php

namespace App\Services;

use App\Models\Estate;
use App\Models\Recommendation;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class RecommendationService
{
    public function __construct(
        private readonly PropertyInteractionService $interactions,//قراءة السلوك المستخدم
       
       
        private readonly RecommendationGeneratorService $generator,
        // هذه التي تولد التوصيات.
        // يعني:
        // تأخذ العقارات
        // ثم ترسلها للتقييم
        // ثم تحفظ النتائج.

        private readonly RecommendationScoringService $scoring,
        //تقييم التوصيات
        //هذا هو محرك الحساب.
        // العقار سعره مناسب؟
            // يعطي:
        // budget_match = 90
    ) {}

    /**
     * @return array{
     *     preferences: ?UserPreference,
     *     behavioral_profile: ?array,
     *     recommendations: LengthAwarePaginator<int, Recommendation>,
     *     match_summary: array<string, mixed>
     * }
     */
    public function getRecommendationsForUser(
        User $user,
        int $perPage = 15,
        bool $forceRefresh = false,
    ): array {
        $preference = $user->preference?->load(['city', 'place']);//. جلب تفضيلات المستخدم
        $behavior = $this->interactions->inferBehavioralProfile($user);//. جلب سلوك المستخدم
        $favoriteEstates = $this->generator->loadActiveFavoriteEstates($user);//. جلب العقارات المفضلة
        $favoriteCount = $favoriteEstates->count();//. عدد العقارات المفضلة 
        // هل نعيد الحساب للتوصيات 
        if ($preference === null && $behavior === null && $favoriteCount === 0) {
            return [
                'preferences' => null,
                'behavioral_profile' => null,
                'recommendations' => $this->emptyRecommendationPaginator($perPage),
                'match_summary' => [
                    'has_preferences' => false,
                    'has_behavioral_data' => false,
                    'has_favorite_estates' => false,
                    'favorite_estates_count' => 0,
                    'stored_recommendations' => false,
                    'message' => 'Add properties to your favorites to get personalized recommendations.',
                ],
            ];
        }

        $generation = null;

        if ($forceRefresh || $this->shouldRegenerate($user)) {
            $generation = $this->generator->generateForUser($user);
        }
        //جلب التوصيات من قاعدة البيانات 
        $recommendations = $this->baseQuery($user)->paginate($perPage); 

        return [
            'preferences' => $preference,
            'behavioral_profile' => $behavior,
            'recommendations' => $recommendations,
            'match_summary' => $this->buildMatchSummary(
                $preference,
                $behavior,
                $favoriteCount,
                $recommendations->total(),
                $generation
            ),
        ];
    }

    /**
     * @return array{
     *     preferences: ?UserPreference,
     *     recommendations: Collection<int, Recommendation>
     * ارجاع اعلى التوصيات فقط 
     */
    public function getTopRecommendations(User $user, int $limit = 5): array
    {
        $preference = $user->preference?->load(['city', 'place']);

        if ($this->shouldRegenerate($user)) {
            $this->generator->generateForUser($user);
        }

        $recommendations = $this->baseQuery($user)
            ->limit($limit)
            ->get();

        return [
            'preferences' => $preference,
            'recommendations' => $recommendations,
        ];
    }

    /**
     * Find estates similar to a given estate (optionally personalized for the user).
     *
     * @return list<array{
     *     estate: Estate,
     *     similarity_score: float,
     *     recommendation_score: ?float,
     *     matching_percentage: ?float,
     *     why_recommended: list<string>
     * هذه للعقارات المشابهة.
     */
    public function getSimilarEstates(User $user, Estate $estate, int $limit = 10): array
    {
        $estate->loadMissing('place.city');
        $preference = $user->preference;
        $favorites = $this->generator->loadActiveFavoriteEstates($user);
        //يجلب مرشحين
        $candidates = Estate::query()
            ->where('status', 'active')
            ->whereKeyNot($estate->id)
            ->with(['place.city'])
            ->when(
                $estate->place?->cities_id,
                fn (Builder $q) => $q->whereHas(
                    'place',
                    fn (Builder $inner) => $inner->where('cities_id', $estate->place->cities_id)
                )
            )
            ->limit((int) config('realestate.similar_estates_pool', 50))
            ->get();

        $results = $candidates->map(function (Estate $candidate) use ($estate, $preference, $user, $favorites) {
            // يحساب التشابه:
            $similarity = $this->scoring->scoreSimilarity($estate, $candidate);
            $personalized = null;

            if ($favorites->isNotEmpty()) {
                $personalized = $this->scoring->scoreAgainstFavorites($candidate, $favorites);
            } elseif ($preference) {
                $personalized = $this->scoring->scoreEstate(
                    $candidate,
                    $preference,
                    $this->interactions->inferBehavioralProfile($user)
                );
            }

            $combined = $personalized
                ? round(($similarity * 0.6) + ($personalized['score'] * 0.4), 2)
                : $similarity;

            $why = ['Similar property type and location'];

            if ($personalized) {
                $why = array_merge($why, $personalized['reasons']);
            }

            return [
                'estate' => $candidate,
                'similarity_score' => $similarity,
                'recommendation_score' => $personalized['score'] ?? null,
                'matching_percentage' => $personalized['matching_percentage'] ?? $similarity,
                'why_recommended' => array_values(array_unique($why)),
                '_combined' => $combined,
            ];
        })
            ->sortByDesc('_combined')
            ->take($limit)
            ->map(function (array $item) {
                unset($item['_combined']);

                return $item;
            })
            ->values()
            ->all();

        return $results;
    }

    /**
     * @return array{generated: int, deactivated: int}
     */
    public function refreshForUser(User $user): array
    {
        return $this->generator->generateForUser($user);
    }
    //هل نعيد الحساب 
    private function shouldRegenerate(User $user): bool
    {
        $hasActive = Recommendation::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->exists();//هل هناك توصيات نشطة

        if (! $hasActive) {
            return true;
        }

        $latest = Recommendation::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->max('updated_at');

        $favoritesUpdatedAt = $user->favoriteEstates()->max('updated_at');

        if ($latest !== null && $favoritesUpdatedAt !== null) {
            if (Carbon::parse($favoritesUpdatedAt)->gt(Carbon::parse($latest))) {
                return true;
            }
        }

        $staleHours = (int) config('realestate.recommendation_stale_hours', 24);

        if ($latest === null) {
            return true;
        }

        return Carbon::parse($latest)->addHours($staleHours)->isPast();
    }

    /**
     * @param  array{generated: int, deactivated: int}|null  $generation
     * @return array<string, mixed>
     * تجهيز معلومات للواجهة 
     */
    private function buildMatchSummary(
        ?UserPreference $preference,
        ?array $behavior,
        int $favoriteCount,
        int $totalStored,
        ?array $generation,
    ): array {
        $summary = [
            'has_preferences' => $preference !== null,
            'has_behavioral_data' => $behavior !== null,
            'has_favorite_estates' => $favoriteCount > 0,
            'favorite_estates_count' => $favoriteCount,
            'stored_recommendations' => true,
            'total_active' => $totalStored,
            'sources' => array_values(array_filter([
                $favoriteCount > 0 ? 'favorite_estates' : null,
                $preference ? 'user_preferences' : null,
                $behavior ? 'property_interactions' : null,
            ])),
            'explicit_filters' => $preference ? array_filter([
                'preferred_city_id' => $preference->cities_id,
                'place_id' => $preference->places_id,
                'min_budget' => $preference->min_budget,
                'max_budget' => $preference->max_budget,
                'preferred_property_type' => $preference->preferred_property_type,
                'investment_goal' => $preference->investment_goal?->value,
                'risk_level' => $preference->risk_level,
            ], fn ($v) => $v !== null) : [],
        ];

        if ($generation !== null) {
            $summary['last_generation'] = $generation;
        }

        if ($favoriteCount > 0) {
            $summary['message'] = 'Recommendations are based on similarity to your favorite properties.';
        }

        return $summary;
    }

    /** @return Builder<Recommendation> */
    private function baseQuery(User $user): Builder
    {
        return Recommendation::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->with([
                'estate.place.city',
                'estate.images',
                'estate.user:id,username,fname,lname',
            ])
            ->orderByDesc('recommendation_score');
    }

    /** @return LengthAwarePaginator<int, Recommendation> */
    //اذا لا يوجد بيانات ارجع فارغ  بدل الخطأ 
    private function emptyRecommendationPaginator(int $perPage): LengthAwarePaginator
    {
        return new LengthAwarePaginator([], 0, $perPage);
    }
}
