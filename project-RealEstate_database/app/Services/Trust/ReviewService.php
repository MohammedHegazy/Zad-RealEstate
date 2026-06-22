<?php

namespace App\Services\Trust;

use App\Models\Agent;
use App\Models\AgentReview;
use App\Models\Companies;
use App\Models\CompanyReview;
use App\Models\Estate;
use App\Models\PropertyReview;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class ReviewService
{
    /**
     * @param  class-string<Model>  $modelClass
     */
    public function listForSubject(
        string $modelClass,
        int $subjectId,
        string $foreignKey,
        int $perPage = 15,
        bool $approvedOnly = true,
    ): LengthAwarePaginator {
        $query = $modelClass::query()
            ->where($foreignKey, $subjectId)
            ->with('user:id,username,fname,lname');

        if ($approvedOnly) {
            $query->approved();
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @return array{average_rating: float|null, reviews_count: int}
     */
    public function ratingSummary(string $modelClass, int $subjectId, string $foreignKey): array
    {
        $query = $modelClass::query()
            ->where($foreignKey, $subjectId)
            ->approved();

        $count = (clone $query)->count();
        $average = $count > 0 ? round((float) (clone $query)->avg('rating'), 2) : null;

        return [
            'average_rating' => $average,
            'reviews_count' => $count,
        ];
    }

    public function createPropertyReview(User $user, Estate $estate, array $data): PropertyReview
    {
        $this->assertNotSelfReview($user, $estate->user_id, 'You cannot review your own property.');
        $this->assertUniqueReview(PropertyReview::class, $user->id, 'estate_id', $estate->id);

        return PropertyReview::create([
            'user_id' => $user->id,
            'estate_id' => $estate->id,
            'rating' => $data['rating'],
            'review' => $data['review'] ?? null,
            'status' => 'pending',
        ]);
    }

    public function createAgentReview(User $user, Agent $agent, array $data): AgentReview
    {
        $this->assertNotSelfReview($user, $agent->user_id, 'You cannot review your own agent profile.');
        $this->assertUniqueReview(AgentReview::class, $user->id, 'agent_id', $agent->id);

        return AgentReview::create([
            'user_id' => $user->id,
            'agent_id' => $agent->id,
            'rating' => $data['rating'],
            'review' => $data['review'] ?? null,
            'status' => 'pending',
        ]);
    }

    public function createCompanyReview(User $user, Companies $company, array $data): CompanyReview
    {
        $this->assertNotSelfReview($user, $company->user_id, 'You cannot review your own company.');
        $this->assertUniqueReview(CompanyReview::class, $user->id, 'company_id', $company->id);

        return CompanyReview::create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'rating' => $data['rating'],
            'review' => $data['review'] ?? null,
            'status' => 'pending',
        ]);
    }

    public function updateReview(Model $review, array $data): Model
    {
        $review->update([
            'rating' => $data['rating'] ?? $review->rating,
            'review' => array_key_exists('review', $data) ? $data['review'] : $review->review,
            'status' => 'pending',
            'admin_notes' => null,
            'reviewed_at' => null,
            'reviewed_by' => null,
        ]);

        return $review->fresh();
    }

    public function deleteReview(Model $review): void
    {
        $this->adminDeleteReview($review);
    }

    public function approveReview(Model $review, ?User $reviewer = null): Model
    {
        $review->update([
            'status' => 'approved',
            'reviewed_at' => now(),
            'reviewed_by' => $reviewer?->id,
        ]);
        $this->recalculateTrustAfterReviewChange($review);

        return $review->fresh();
    }

    public function rejectReview(Model $review, ?string $adminNotes = null, ?User $reviewer = null): Model
    {
        $review->update([
            'status' => 'rejected',
            'admin_notes' => $adminNotes,
            'reviewed_at' => now(),
            'reviewed_by' => $reviewer?->id,
        ]);
        $this->recalculateTrustAfterReviewChange($review);

        return $review->fresh();
    }

    public function adminDeleteReview(Model $review): void
    {
        $wasApproved = $review->isApproved();
        $agentId = $review instanceof AgentReview ? $review->agent_id : null;
        $companyId = $review instanceof CompanyReview ? $review->company_id : null;

        $review->delete();

        if (! $wasApproved) {
            return;
        }

        $trustScore = app(TrustScoreService::class);

        if ($agentId) {
            $agent = Agent::find($agentId);
            if ($agent) {
                $trustScore->recalculateForAgent($agent);
            }
        }

        if ($companyId) {
            $company = Companies::find($companyId);
            if ($company) {
                $trustScore->recalculateForCompany($company);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function adminListReviews(string $type, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->adminReviewQuery($type);
        $this->applyAdminReviewFilters($query, $type, $filters);

        return $query->latest()->paginate($perPage);
    }

    public function adminShowReview(string $type, int $id): Model
    {
        return $this->adminReviewQuery($type)->findOrFail($id);
    }

    public function pendingReviews(?string $type = null, int $perPage = 15): LengthAwarePaginator
    {
        return $this->adminListReviews($type ?? 'property', ['status' => 'pending'], $perPage);
    }

    /**
     * @return Builder<PropertyReview|AgentReview|CompanyReview>
     */
    private function adminReviewQuery(string $type): Builder
    {
        return match ($type) {
            'agent' => AgentReview::query()->with(['user', 'agent.user', 'reviewer:id,username,fname,lname']),
            'company' => CompanyReview::query()->with(['user', 'company', 'reviewer:id,username,fname,lname']),
            'property' => PropertyReview::query()->with(['user', 'estate', 'reviewer:id,username,fname,lname']),
            default => throw new InvalidArgumentException('Invalid review type. Use property, agent, or company.'),
        };
    }

    /**
     * @param  Builder<PropertyReview|AgentReview|CompanyReview>  $query
     * @param  array<string, mixed>  $filters
     */
    private function applyAdminReviewFilters(Builder $query, string $type, array $filters): void
    {
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', (int) $filters['user_id']);
        }

        if ($type === 'property' && ! empty($filters['estate_id'])) {
            $query->where('estate_id', (int) $filters['estate_id']);
        }

        if ($type === 'agent' && ! empty($filters['agent_id'])) {
            $query->where('agent_id', (int) $filters['agent_id']);
        }

        if ($type === 'company' && ! empty($filters['company_id'])) {
            $query->where('company_id', (int) $filters['company_id']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $builder) use ($search, $type) {
                $builder->where('review', 'like', "%{$search}%")
                    ->orWhereHas('user', function (Builder $userQuery) use ($search) {
                        $userQuery->where('username', 'like', "%{$search}%")
                            ->orWhere('fname', 'like', "%{$search}%")
                            ->orWhere('lname', 'like', "%{$search}%");
                    });

                if ($type === 'property') {
                    $builder->orWhereHas('estate', fn (Builder $estateQuery) => $estateQuery->where('name', 'like', "%{$search}%"));
                }

                if ($type === 'company') {
                    $builder->orWhereHas('company', fn (Builder $companyQuery) => $companyQuery->where('company_name', 'like', "%{$search}%"));
                }
            });
        }
    }

    private function assertNotSelfReview(User $user, int $ownerId, string $message): void
    {
        if ($user->id === $ownerId) {
            throw new InvalidArgumentException($message);
        }
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    private function assertUniqueReview(string $modelClass, int $userId, string $foreignKey, int $subjectId): void
    {
        $exists = $modelClass::query()
            ->where('user_id', $userId)
            ->where($foreignKey, $subjectId)
            ->exists();

        if ($exists) {
            throw new InvalidArgumentException('You have already submitted a review.');
        }
    }

    private function recalculateTrustAfterReviewChange(Model $review): void
    {
        $trustScore = app(TrustScoreService::class);

        if ($review instanceof AgentReview) {
            $trustScore->recalculateForAgent($review->agent);
        }

        if ($review instanceof CompanyReview) {
            $trustScore->recalculateForCompany($review->company);
        }
    }
}
