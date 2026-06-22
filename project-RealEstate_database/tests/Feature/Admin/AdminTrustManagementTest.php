<?php

namespace Tests\Feature\Admin;

use App\Models\Agent;
use App\Models\AgentReview;
use App\Models\CompanyReview;
use App\Models\PropertyReview;
use App\Models\User;
use App\Models\VerificationRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\InteractsWithTrustApi;
use Tests\TestCase;

class AdminTrustManagementTest extends TestCase
{
    use InteractsWithTrustApi;
    use RefreshDatabase;

    public function test_admin_can_list_reviews_by_status(): void
    {
        $admin = User::factory()->admin()->create();
        PropertyReview::factory()->create(['status' => 'approved']);
        PropertyReview::factory()->create(['status' => 'pending']);

        $response = $this->actingAsApiUser($admin)->getJson(
            $this->adminApiPrefix().'/trust/reviews?type=property&status=approved'
        );

        $this->assertApiSuccess($response);
        $this->assertCount(1, $response->json('data'));
        $this->assertSame('approved', $response->json('data.0.status'));
    }

    public function test_admin_can_reject_review_with_admin_notes(): void
    {
        $admin = User::factory()->admin()->create();
        $review = PropertyReview::factory()->create(['status' => 'pending']);

        $response = $this->actingAsApiUser($admin)->postJson(
            $this->adminApiPrefix().'/trust/property-reviews/'.$review->id.'/reject',
            ['admin_notes' => 'Inappropriate content.'],
        );

        $this->assertApiSuccess($response);
        $this->assertDatabaseHas('property_reviews', [
            'id' => $review->id,
            'status' => 'rejected',
            'admin_notes' => 'Inappropriate content.',
            'reviewed_by' => $admin->id,
        ]);
        $this->assertNotNull($review->fresh()->reviewed_at);
    }

    public function test_admin_can_delete_agent_review_and_recalculate_trust(): void
    {
        $admin = User::factory()->admin()->create();
        $agent = Agent::factory()->create(['trust_score' => 40]);
        $review = AgentReview::factory()->create([
            'agent_id' => $agent->id,
            'status' => 'approved',
            'rating' => 5,
        ]);

        $response = $this->actingAsApiUser($admin)->deleteJson(
            $this->adminApiPrefix().'/trust/agent-reviews/'.$review->id
        );

        $this->assertApiSuccess($response);
        $this->assertDatabaseMissing('agent_reviews', ['id' => $review->id]);
    }

    public function test_admin_can_list_verifications_with_status_filter(): void
    {
        $admin = User::factory()->admin()->create();
        VerificationRequest::factory()->create(['status' => 'approved']);
        VerificationRequest::factory()->create(['status' => 'pending']);

        $response = $this->actingAsApiUser($admin)->getJson(
            $this->adminApiPrefix().'/trust/verifications?status=rejected'
        );

        $this->assertApiSuccess($response);
        $this->assertCount(0, $response->json('data'));
    }

    public function test_admin_can_download_verification_document(): void
    {
        Storage::fake('local');

        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $path = 'verification-documents/'.$user->id.'/id.pdf';
        Storage::disk('local')->put($path, 'pdf-content');

        $verification = VerificationRequest::factory()->create([
            'user_id' => $user->id,
            'document_path' => $path,
        ]);

        $response = $this->actingAsApiUser($admin)->get(
            $this->adminApiPrefix().'/trust/verifications/'.$verification->id.'/document'
        );

        $response->assertOk();
        $response->assertHeader('content-disposition');
    }

    public function test_admin_can_recalculate_agent_trust_score(): void
    {
        $admin = User::factory()->admin()->create();
        $agent = Agent::factory()->create(['trust_score' => 0]);
        AgentReview::factory()->create([
            'agent_id' => $agent->id,
            'status' => 'approved',
            'rating' => 5,
        ]);

        $response = $this->actingAsApiUser($admin)->postJson(
            $this->adminApiPrefix().'/trust/agents/'.$agent->id.'/recalculate-trust'
        );

        $this->assertApiSuccess($response);
        $this->assertGreaterThan(0, $agent->fresh()->trust_score);
        $response->assertJsonPath('data.trust_score', $agent->fresh()->trust_score);
    }
}
