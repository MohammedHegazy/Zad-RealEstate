<?php

namespace Tests\Feature\SocialLinks;

use App\Models\Estate;
use App\Models\SocialLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithSocialLinkApi;
use Tests\TestCase;

class EstateSocialLinkTest extends TestCase
{
    use InteractsWithSocialLinkApi;
    use RefreshDatabase;

    public function test_owner_can_update_estate_social_links_via_legacy_endpoint(): void
    {
        $owner = User::factory()->create();
        $estate = Estate::factory()->forOwner($owner)->create();

        $response = $this->actingAsApiUser($owner)
            ->putJson($this->apiPrefix().'/my/estates/'.$estate->id.'/social-media', [
                'facebook' => 'https://facebook.com/listing',
                'instagram' => 'https://instagram.com/listing',
            ]);

        $this->assertApiSuccess($response);
        $this->assertDatabaseHas('social_links', [
            'socialable_type' => Estate::class,
            'socialable_id' => $estate->id,
            'platform' => 'facebook',
        ]);
    }

    public function test_non_owner_cannot_update_estate_social_links(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $estate = Estate::factory()->forOwner($owner)->create();

        $response = $this->actingAsApiUser($other)
            ->putJson($this->apiPrefix().'/my/estates/'.$estate->id.'/social-media', [
                'facebook' => 'https://facebook.com/hacked',
            ]);

        $this->assertApiError($response, 403);
    }

    public function test_guest_cannot_update_estate_social_links(): void
    {
        $estate = Estate::factory()->create();

        $this->putJson($this->apiPrefix().'/my/estates/'.$estate->id.'/social-media', [
            'facebook' => 'https://facebook.com/x',
        ])->assertUnauthorized();
    }

    public function test_estate_social_links_use_polymorphic_backend(): void
    {
        $owner = User::factory()->create();
        $estate = Estate::factory()->forOwner($owner)->create();

        $this->actingAsApiUser($owner)
            ->putJson($this->apiPrefix().'/my/estates/'.$estate->id.'/social-media', [
                'links' => [
                    ['platform' => 'youtube', 'url' => 'https://youtube.com/@listing'],
                ],
            ])
            ->assertOk();

        $link = SocialLink::query()
            ->where('socialable_type', Estate::class)
            ->where('socialable_id', $estate->id)
            ->where('platform', 'youtube')
            ->first();

        $this->assertNotNull($link);
        $this->assertSame('https://youtube.com/@listing', $link->url);
    }

    public function test_estate_social_links_relationship(): void
    {
        $estate = Estate::factory()->create();
        SocialLink::factory()->forSocialable($estate)->platform('facebook')->create();
        SocialLink::factory()->forSocialable($estate)->platform('instagram')->create();

        $this->assertCount(2, $estate->fresh()->socialLinks);
    }
}
