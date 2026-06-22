<?php

namespace App\Http\Controllers\Api;

use App\Models\Estate;
use App\Models\Recommendation;
use App\Services\RecommendationService;
use App\Traits\FormatsRecommendationResponse;
use App\Traits\FormatsUserPreferenceResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecommendationController extends BaseApiController
{
    use FormatsRecommendationResponse, FormatsUserPreferenceResponse;

    public function __construct(
        private readonly RecommendationService $recommendations,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $result = $this->recommendations->getRecommendationsForUser(
            $request->user(),
            $request->integer('per_page', 15),
            $request->boolean('refresh') //اعادة التوليد للمستخدم لا تستخدم القديم استخدم الجديد
        );

        return $this->formatListResponse($result, 'Recommendations retrieved.');
    }

    public function top(Request $request): JsonResponse
    {
        $result = $this->recommendations->getTopRecommendations(
            $request->user(),
            $request->integer('limit', 5),
        );

        return $this->successResponse(
            [
                'preferences' => $result['preferences']
                    ? $this->formatUserPreference($result['preferences'])//تعرض تفضيلات المستخدم
                    : null,
                'recommendations' => $result['recommendations']
                    ->map(fn (Recommendation $r) => $this->formatRecommendation($r))
                    ->values()
                    ->all(),
            ],
            'Top recommendations retrieved.'
        );
    }

    public function similarEstates(Request $request, Estate $estate): JsonResponse
    {
        if ($estate->status !== 'active') {
            return $this->notFoundResponse('Estate not available for recommendations.');
        }

        $items = $this->recommendations->getSimilarEstates(
            $request->user(),
            $estate,
            $request->integer('limit', 10), 
        );

        return $this->successResponse(
            [
                'source_estate_id' => $estate->id,
                'similar_estates' => collect($items)->map(fn (array $item) => [
                    'similarity_score' => $item['similarity_score'],
                    'recommendation_score' => $item['recommendation_score'],
                    'matching_percentage' => $item['matching_percentage'],
                    'why_recommended' => $item['why_recommended'],
                    'estate' => $this->formatRecommendationEstate($item['estate']),
                ])->values()->all(),
            ],
            'Similar estates retrieved.'
        );
    }

    public function show(Request $request, Recommendation $recommendation): JsonResponse
    {
        if ($recommendation->user_id !== $request->user()->id || ! $recommendation->is_active) {
            return $this->notFoundResponse('Recommendation not found.');
        }
        //تحميل العقار المقترح
        $recommendation->load([
            'estate.place.city',
            'estate.user:id,username,fname,lname',
        ]);

        return $this->successResponse(
            $this->formatRecommendation($recommendation),
            'Recommendation retrieved.'
        );
    }

    public function refresh(Request $request): JsonResponse
    {
        $generation = $this->recommendations->refreshForUser($request->user());

        $result = $this->recommendations->getRecommendationsForUser(
            $request->user(),
            $request->integer('per_page', 15),
            forceRefresh: false
        );

        $result['match_summary']['last_generation'] = $generation;

        return $this->formatListResponse(
            $result,
            "Recommendations refreshed. {$generation['generated']} properties saved."
        );
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function formatListResponse(array $result, string $message): JsonResponse//تعرض التوصيات للمستخدم
    {
        $paginator = $result['recommendations'];
        $preference = $result['preferences'];
        $behavior = $result['behavioral_profile'];
        $hasSignals = $preference !== null
            || $behavior !== null
            || ($result['match_summary']['has_favorite_estates'] ?? false);

        return $this->successResponse(
            [
                'preferences' => $preference
                    ? $this->formatUserPreference($preference)
                    : null,
                'behavioral_profile' => $behavior,
                'match_summary' => $result['match_summary'],
                'recommendations' => collect($paginator->items())
                    ->map(fn (Recommendation $r) => $this->formatRecommendation($r))
                    ->values()
                    ->all(),
            ],
            $hasSignals ? $message : 'Add properties to your favorites to get personalized recommendations.',
            200,
            $this->paginationMeta($paginator)
        );
    }
}
