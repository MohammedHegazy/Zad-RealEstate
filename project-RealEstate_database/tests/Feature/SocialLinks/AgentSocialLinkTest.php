<?php

namespace Tests\Feature\SocialLinks;

use App\Models\Agent;
use App\Models\SocialLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithSocialLinkApi;
use Tests\TestCase;

class AgentSocialLinkTest extends TestCase
{
    use InteractsWithSocialLinkApi;
    use RefreshDatabase;

    public function test_agent_social_links_relationship(): void
    {
        $agent = Agent::factory()->create();
        SocialLink::factory()->forSocialable($agent)->platform('linkedin')->create();

        $this->assertCount(1, $agent->fresh()->socialLinks);
        $this->assertSame(Agent::class, $agent->socialLinks->first()->socialable_type);
    }

    public function test_admin_can_create_agent_social_link(): void
    {
        $admin = User::factory()->admin()->create();
        $agent = Agent::factory()->create();

        $response = $this->actingAsApiUser($admin)
            ->postJson($this->adminApiPrefix().'/social-links', [
                'socialable_type' => Agent::class,
                'socialable_id' => $agent->id,
                'platform' => 'twitter',
                'url' => 'https://twitter.com/agentprofile',
            ]);

        $this->assertApiSuccess($response, 201);
        $this->assertDatabaseHas('social_links', [
            'socialable_type' => Agent::class,
            'socialable_id' => $agent->id,
            'platform' => 'twitter',
        ]);
    }

    public function test_regular_user_cannot_use_admin_social_link_store(): void
    {
        $user = User::factory()->create();
        $agent = Agent::factory()->create();

        $response = $this->actingAsApiUser($user)
            ->postJson($this->adminApiPrefix().'/social-links', [
                'socialable_type' => Agent::class,
                'socialable_id' => $agent->id,
                'platform' => 'facebook',
                'url' => 'https://facebook.com/agent',
            ]);

        $response->assertStatus(403);
        $response->assertJsonPath('success', false);
    }
}
