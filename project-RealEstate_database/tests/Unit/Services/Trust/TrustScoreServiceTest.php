<?php

namespace Tests\Unit\Services\Trust;

use App\Models\Agent;
use App\Models\AgentReview;
use App\Models\Companies;
use App\Models\CompanyReview;
use App\Models\Estate;
use App\Models\User;
use App\Services\Trust\TrustScoreService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrustScoreServiceTest extends TestCase
{
    use RefreshDatabase;

    private TrustScoreService $trustScore;

    protected function setUp(): void
    {
        parent::setUp();
        $this->trustScore = app(TrustScoreService::class);
    }

    public function test_verified_user_increases_agent_trust_score(): void
    {
        $owner = User::factory()->create(['is_verified' => true]);
        $agent = Agent::factory()->create(['user_id' => $owner->id]);

        Estate::factory()->forOwner($owner)->count(2)->create(['status' => 'active']);
        AgentReview::factory()->approved()->create([
            'agent_id' => $agent->id,
            'rating' => 5,
        ]);

        $result = $this->trustScore->forAgent($agent);

        $this->assertGreaterThan(0, $result['trust_score']);
        $this->assertTrue($result['is_verified']);
        $this->assertSame(1, $result['reviews_count']);
        $this->assertSame(25, $result['factors']['verified_account']);
    }

    public function test_company_trust_score_is_persisted(): void
    {
        $owner = User::factory()->create(['is_verified' => true]);
        $company = Companies::factory()->create(['user_id' => $owner->id]);

        CompanyReview::factory()->approved()->count(2)->create([
            'company_id' => $company->id,
            'rating' => 4,
        ]);

        $result = $this->trustScore->forCompany($company);

        $this->assertGreaterThan(0, $result['trust_score']);
        $this->assertSame($result['trust_score'], $company->fresh()->trust_score);
    }
}
