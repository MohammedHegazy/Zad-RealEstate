<?php

namespace Tests\Feature\Trust;

use App\Models\Agent;
use App\Models\AgentReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTrustApi;
use Tests\TestCase;

class AgentReviewTest extends TestCase
{
    use InteractsWithTrustApi;
    use RefreshDatabase;

    public function test_user_can_submit_agent_review(): void
    {
        $reviewer = User::factory()->create();
        $agentOwner = User::factory()->create();
        $agent = Agent::factory()->create(['user_id' => $agentOwner->id]);

        $response = $this->actingAsApiUser($reviewer)->postJson(
            $this->apiPrefix().'/agents/'.$agent->id.'/reviews',
            ['rating' => 4, 'review' => 'Professional agent']
        );

        $this->assertApiSuccess($response, 201);
        $this->assertDatabaseHas('agent_reviews', [
            'agent_id' => $agent->id,
            'user_id' => $reviewer->id,
            'status' => 'pending',
        ]);
    }

    public function test_agent_rating_summary(): void
    {
        $agent = Agent::factory()->create();
        AgentReview::factory()->approved()->create(['agent_id' => $agent->id, 'rating' => 5]);
        AgentReview::factory()->approved()->create(['agent_id' => $agent->id, 'rating' => 3]);

        $response = $this->getJson($this->apiPrefix().'/agents/'.$agent->id.'/reviews/summary');

        $this->assertApiSuccess($response);
        $this->assertEquals(4.0, $response->json('data.average_rating'));
        $this->assertSame(2, $response->json('data.reviews_count'));
    }

    public function test_user_cannot_submit_duplicate_agent_review(): void
    {
        $reviewer = User::factory()->create();
        $agent = Agent::factory()->create();

        $this->actingAsApiUser($reviewer)->postJson(
            $this->apiPrefix().'/agents/'.$agent->id.'/reviews',
            ['rating' => 4, 'review' => 'Great agent'],
        )->assertStatus(201);

        $response = $this->actingAsApiUser($reviewer)->postJson(
            $this->apiPrefix().'/agents/'.$agent->id.'/reviews',
            ['rating' => 5, 'review' => 'Updated opinion'],
        );

        $response->assertStatus(422);
        $this->assertDatabaseCount('agent_reviews', 1);
    }
}
