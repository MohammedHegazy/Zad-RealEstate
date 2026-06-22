<?php

namespace Tests\Feature\Agents;

use App\Models\Agent;
use App\Models\AgentReview;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentReviewDatabaseIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_unique_user_agent_pair_constraint(): void
    {
        $user = User::factory()->create();
        $agent = Agent::factory()->create();

        AgentReview::query()->create([
            'user_id' => $user->id,
            'agent_id' => $agent->id,
            'rating' => 4,
            'status' => 'pending',
        ]);

        $this->expectException(QueryException::class);

        AgentReview::query()->create([
            'user_id' => $user->id,
            'agent_id' => $agent->id,
            'rating' => 5,
            'status' => 'pending',
        ]);
    }

    public function test_average_rating_uses_only_approved_reviews(): void
    {
        $agent = Agent::factory()->create();

        AgentReview::factory()->approved()->create(['agent_id' => $agent->id, 'rating' => 5]);
        AgentReview::factory()->approved()->create(['agent_id' => $agent->id, 'rating' => 3]);
        AgentReview::factory()->create(['agent_id' => $agent->id, 'rating' => 1, 'status' => 'pending']);

        $agent->loadAvg('approvedReviews', 'rating')->loadCount('approvedReviews');

        $this->assertSame(4.0, (float) $agent->approved_reviews_avg_rating);
        $this->assertSame(2, $agent->approved_reviews_count);
    }

    public function test_deleting_user_cascades_agent_reviews(): void
    {
        $user = User::factory()->create();
        AgentReview::factory()->create(['user_id' => $user->id]);

        $user->delete();

        $this->assertDatabaseCount('agent_reviews', 0);
    }

    public function test_deleting_agent_cascades_agent_reviews(): void
    {
        $agent = Agent::factory()->create();
        AgentReview::factory()->create(['agent_id' => $agent->id]);

        $agent->delete();

        $this->assertDatabaseCount('agent_reviews', 0);
    }
}
