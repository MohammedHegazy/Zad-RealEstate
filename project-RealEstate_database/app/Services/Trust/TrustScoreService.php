<?php

namespace App\Services\Trust;

use App\Models\Agent;
use App\Models\AgentReview;
use App\Models\Companies;
use App\Models\CompanyReview;
use App\Models\Estate;
use App\Models\PropertyInteraction;
use App\Models\User;
use App\Models\VerificationRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TrustScoreService
{
    /**
     * @return array<string, mixed>
     */
    public function forAgent(Agent $agent): array
    {
        $agent->loadMissing('user');
        $score = $this->calculateAgentScore($agent);
        $agent->update(['trust_score' => $score['trust_score']]);

        return $score;
    }

    /**
     * @return array<string, mixed>
     */
    public function forCompany(Companies $company): array
    {
        $company->loadMissing('user');
        $score = $this->calculateCompanyScore($company);
        $company->update(['trust_score' => $score['trust_score']]);

        return $score;
    }

    public function recalculateForAgent(Agent $agent): array
    {
        return $this->forAgent($agent);
    }

    public function recalculateForCompany(Companies $company): array
    {
        return $this->forCompany($company);
    }

    public function recalculateForUser(User $user): void
    {
        $user->load(['agent', 'company']);

        if ($user->agent) {
            $this->recalculateForAgent($user->agent);
        }

        if ($user->company) {
            $this->recalculateForCompany($user->company);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function calculateAgentScore(Agent $agent): array
    {
        $user = $agent->user;
        $weights = config('realestate.trust_score.weights');

        $approvedReviews = AgentReview::query()
            ->where('agent_id', $agent->id)
            ->approved();

        $reviewCount = (clone $approvedReviews)->count();
        $averageRating = $reviewCount > 0 ? (float) (clone $approvedReviews)->avg('rating') : null;

        $approvedProperties = Estate::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->count();

        $activity = (int) $agent->views
            + (int) $agent->shares
            + PropertyInteraction::query()->where('user_id', $user->id)->count();

        $factors = [
            'verified_account' => $this->verifiedScore($user, $weights['verified_account']),
            'approved_properties' => $this->scaledScore(
                $approvedProperties,
                (int) config('realestate.trust_score.max_approved_properties'),
                $weights['approved_properties'],
            ),
            'average_rating' => $this->ratingScore($averageRating, $weights['average_rating']),
            'review_count' => $this->scaledScore(
                $reviewCount,
                (int) config('realestate.trust_score.max_review_count'),
                $weights['review_count'],
            ),
            'platform_activity' => $this->scaledScore(
                $activity,
                (int) config('realestate.trust_score.max_activity_score'),
                $weights['platform_activity'],
            ),
        ];

        return $this->buildResult('agent', $agent->id, $factors, $averageRating, $reviewCount, (bool) $user->is_verified);
    }

    /**
     * @return array<string, mixed>
     */
    private function calculateCompanyScore(Companies $company): array
    {
        $user = $company->user;
        $weights = config('realestate.trust_score.weights');

        $approvedReviews = CompanyReview::query()
            ->where('company_id', $company->id)
            ->approved();

        $reviewCount = (clone $approvedReviews)->count();
        $averageRating = $reviewCount > 0 ? (float) (clone $approvedReviews)->avg('rating') : null;

        $approvedProperties = Estate::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->count();

        $agentActivity = $company->agents()->sum('views') + $company->agents()->sum('shares');
        $activity = (int) $agentActivity + PropertyInteraction::query()->where('user_id', $user->id)->count();

        $factors = [
            'verified_account' => $this->verifiedScore($user, $weights['verified_account']),
            'approved_properties' => $this->scaledScore(
                $approvedProperties,
                (int) config('realestate.trust_score.max_approved_properties'),
                $weights['approved_properties'],
            ),
            'average_rating' => $this->ratingScore($averageRating, $weights['average_rating']),
            'review_count' => $this->scaledScore(
                $reviewCount,
                (int) config('realestate.trust_score.max_review_count'),
                $weights['review_count'],
            ),
            'platform_activity' => $this->scaledScore(
                $activity,
                (int) config('realestate.trust_score.max_activity_score'),
                $weights['platform_activity'],
            ),
        ];

        return $this->buildResult('company', $company->id, $factors, $averageRating, $reviewCount, (bool) $user->is_verified);
    }

    private function verifiedScore(User $user, int $weight): int
    {
        return $user->is_verified ? $weight : 0;
    }

    private function ratingScore(?float $averageRating, int $weight): int
    {
        if ($averageRating === null) {
            return 0;
        }

        return (int) round(($averageRating / 5) * $weight);
    }

    private function scaledScore(int $value, int $max, int $weight): int
    {
        if ($max <= 0) {
            return 0;
        }

        $ratio = min($value / $max, 1);

        return (int) round($ratio * $weight);
    }

    /**
     * @param  array<string, int>  $factors
     * @return array<string, mixed>
     */
    private function buildResult(
        string $entityType,
        int $entityId,
        array $factors,
        ?float $averageRating,
        int $reviewCount,
        bool $isVerified,
    ): array {
        $total = min(array_sum($factors), 100);

        return [
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'trust_score' => $total,
            'average_rating' => $averageRating,
            'reviews_count' => $reviewCount,
            'is_verified' => $isVerified,
            'factors' => $factors,
        ];
    }
}
