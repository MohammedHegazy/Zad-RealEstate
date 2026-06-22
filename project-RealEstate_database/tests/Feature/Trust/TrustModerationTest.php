<?php

namespace Tests\Feature\Trust;

use App\Models\Agent;
use App\Models\AgentReview;
use App\Models\PropertyReview;
use App\Models\User;
use App\Models\VerificationRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTrustApi;
use Tests\TestCase;

class TrustModerationTest extends TestCase
{
    use InteractsWithTrustApi;
    use RefreshDatabase;

    public function test_admin_can_list_pending_reviews(): void
    {
        $admin = User::factory()->admin()->create();
        PropertyReview::factory()->create(['status' => 'pending']);

        $response = $this->actingAsApiUser($admin)->getJson(
            $this->adminApiPrefix().'/trust/reviews/pending?type=property'
        );

        $this->assertApiSuccess($response);
        $this->assertGreaterThan(0, count($response->json('data')));
    }

    public function test_admin_can_approve_agent_review_and_update_trust_score(): void
    {
        $admin = User::factory()->admin()->create();
        $agentOwner = User::factory()->create(['is_verified' => true]);
        $agent = Agent::factory()->create(['user_id' => $agentOwner->id, 'trust_score' => 0]);
        $review = AgentReview::factory()->create([
            'agent_id' => $agent->id,
            'rating' => 5,
            'status' => 'pending',
        ]);

        $response = $this->actingAsApiUser($admin)->postJson(
            $this->adminApiPrefix().'/trust/agent-reviews/'.$review->id.'/approve'
        );

        $this->assertApiSuccess($response);
        $this->assertSame('approved', $review->fresh()->status);
        $this->assertGreaterThan(0, $agent->fresh()->trust_score);
    }

    public function test_admin_can_approve_verification_request(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['is_verified' => false]);
        $request = VerificationRequest::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAsApiUser($admin)->postJson(
            $this->adminApiPrefix().'/trust/verifications/'.$request->id.'/approve'
        );

        $this->assertApiSuccess($response);
        $this->assertTrue($user->fresh()->is_verified);
        $this->assertSame('approved', $request->fresh()->status);
        $this->assertSame($admin->id, $request->fresh()->reviewed_by);
        $this->assertNotNull($request->fresh()->reviewed_at);
        $response->assertJsonPath('data.reviewer.id', $admin->id);
    }

    public function test_admin_can_reject_verification_request_with_audit_fields(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['is_verified' => false]);
        $verificationRequest = VerificationRequest::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAsApiUser($admin)->postJson(
            $this->adminApiPrefix().'/trust/verifications/'.$verificationRequest->id.'/reject',
            ['admin_notes' => 'Document unreadable.'],
        );

        $this->assertApiSuccess($response);
        $this->assertSame('rejected', $verificationRequest->fresh()->status);
        $this->assertSame($admin->id, $verificationRequest->fresh()->reviewed_by);
        $this->assertNotNull($verificationRequest->fresh()->reviewed_at);
        $this->assertFalse($user->fresh()->is_verified);
        $response->assertJsonPath('data.reviewer.id', $admin->id);
    }

    public function test_reviewer_reference_is_nulled_when_admin_is_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $verificationRequest = VerificationRequest::factory()->create([
            'status' => 'approved',
            'reviewed_at' => now(),
            'reviewed_by' => $admin->id,
        ]);

        $admin->delete();

        $this->assertDatabaseHas('verification_requests', [
            'id' => $verificationRequest->id,
            'status' => 'approved',
            'reviewed_by' => null,
        ]);
        $this->assertNotNull($verificationRequest->fresh()->reviewed_at);
    }

    public function test_non_admin_cannot_moderate_reviews(): void
    {
        $user = User::factory()->create();
        $review = PropertyReview::factory()->create();

        $this->actingAsApiUser($user)->postJson(
            $this->adminApiPrefix().'/trust/property-reviews/'.$review->id.'/approve'
        )->assertForbidden();
    }
}
