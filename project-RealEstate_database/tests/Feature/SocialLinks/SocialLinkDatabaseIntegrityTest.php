<?php

namespace Tests\Feature\SocialLinks;

use App\Models\Agent;
use App\Models\Companies;
use App\Models\Estate;
use App\Models\SocialLink;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialLinkDatabaseIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_morph_to_resolves_parent_user(): void
    {
        $user = User::factory()->create();
        $link = SocialLink::factory()->forSocialable($user)->create();

        $this->assertInstanceOf(User::class, $link->socialable);
        $this->assertTrue($link->socialable->is($user));
    }

    public function test_unique_platform_per_entity_constraint(): void
    {
        $user = User::factory()->create();
        SocialLink::factory()->forSocialable($user)->platform('facebook')->create();

        $this->expectException(QueryException::class);

        SocialLink::factory()->forSocialable($user)->platform('facebook')->create();
    }

    public function test_deleting_user_cascades_social_links(): void
    {
        $user = User::factory()->create();
        SocialLink::factory()->forSocialable($user)->count(2)->create();

        $user->delete();

        $this->assertDatabaseCount('social_links', 0);
    }

    public function test_deleting_company_cascades_social_links(): void
    {
        $company = Companies::factory()->create();
        SocialLink::factory()->forSocialable($company)->count(2)->create();

        $company->delete();

        $this->assertDatabaseCount('social_links', 0);
    }

    public function test_deleting_estate_cascades_social_links(): void
    {
        $estate = Estate::factory()->create();
        SocialLink::factory()->forSocialable($estate)->create();

        $estate->delete();

        $this->assertDatabaseCount('social_links', 0);
    }

    public function test_deleting_agent_cascades_social_links(): void
    {
        $agent = Agent::factory()->create();
        SocialLink::factory()->forSocialable($agent)->create();

        $agent->delete();

        $this->assertDatabaseCount('social_links', 0);
    }

    public function test_same_platform_allowed_on_different_entities(): void
    {
        $user = User::factory()->create();
        $company = Companies::factory()->create();

        SocialLink::factory()->forSocialable($user)->platform('facebook')->create();
        SocialLink::factory()->forSocialable($company)->platform('facebook')->create();

        $this->assertDatabaseCount('social_links', 2);
    }

    public function test_polymorphic_ownership_is_isolated_per_entity(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $linkA = SocialLink::factory()->forSocialable($userA)->platform('facebook')->create();
        SocialLink::factory()->forSocialable($userB)->platform('facebook')->create();

        $this->assertCount(1, $userA->socialLinks);
        $this->assertTrue($userA->socialLinks->first()->is($linkA));
        $this->assertCount(1, $userB->socialLinks);
    }
}
