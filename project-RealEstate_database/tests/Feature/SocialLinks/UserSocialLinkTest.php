<?php

namespace Tests\Feature\SocialLinks;

use App\Models\SocialLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithSocialLinkApi;
use Tests\TestCase;

class UserSocialLinkTest extends TestCase
{
    use InteractsWithSocialLinkApi;
    use RefreshDatabase;

    public function test_guest_cannot_access_my_social_media(): void
    {
        $response = $this->getJson($this->apiPrefix().'/my/social-media');

        $response->assertUnauthorized();
    }

    public function test_user_can_list_own_social_links_via_legacy_endpoint(): void
    {
        $user = User::factory()->create();
        SocialLink::factory()->forSocialable($user)->platform('facebook')->create();
        SocialLink::factory()->forSocialable($user)->platform('instagram')->create();

        $response = $this->actingAsApiUser($user)
            ->getJson($this->apiPrefix().'/my/social-media');

        $this->assertApiSuccess($response);
        $response->assertJsonCount(2, 'data');
        $response->assertJsonFragment(['platform' => 'facebook']);
        $response->assertJsonFragment(['platform' => 'instagram']);
    }

    public function test_user_can_sync_links_via_legacy_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsApiUser($user)
            ->putJson($this->apiPrefix().'/my/social-media', [
                'facebook' => 'https://facebook.com/johndoe',
                'instagram' => 'https://instagram.com/johndoe',
            ]);

        $this->assertApiSuccess($response);
        $this->assertDatabaseHas('social_links', [
            'socialable_type' => User::class,
            'socialable_id' => $user->id,
            'platform' => 'facebook',
            'url' => 'https://facebook.com/johndoe',
        ]);
        $this->assertDatabaseHas('social_links', [
            'socialable_type' => User::class,
            'socialable_id' => $user->id,
            'platform' => 'instagram',
        ]);
    }

    public function test_user_can_sync_links_array(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsApiUser($user)
            ->putJson($this->apiPrefix().'/my/social-media', [
                'links' => [
                    ['platform' => 'linkedin', 'url' => 'https://linkedin.com/in/johndoe'],
                    ['platform' => 'website', 'url' => 'https://example.com'],
                ],
            ]);

        $this->assertApiSuccess($response);
        $response->assertJsonCount(2, 'data');
    }

    public function test_sync_replaces_existing_links(): void
    {
        $user = User::factory()->create();
        SocialLink::factory()->forSocialable($user)->platform('facebook')->create();

        $this->actingAsApiUser($user)
            ->putJson($this->apiPrefix().'/my/social-media', [
                'links' => [
                    ['platform' => 'twitter', 'url' => 'https://twitter.com/johndoe'],
                ],
            ])
            ->assertOk();

        $this->assertDatabaseMissing('social_links', [
            'socialable_id' => $user->id,
            'platform' => 'facebook',
        ]);
        $this->assertDatabaseHas('social_links', [
            'socialable_id' => $user->id,
            'platform' => 'twitter',
        ]);
    }

    public function test_invalid_platform_is_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsApiUser($user)
            ->putJson($this->apiPrefix().'/my/social-media', [
                'links' => [
                    ['platform' => 'invalid_platform', 'url' => 'https://example.com'],
                ],
            ]);

        $this->assertValidationFailed($response);
    }

    public function test_invalid_url_is_rejected_in_links_array(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsApiUser($user)
            ->putJson($this->apiPrefix().'/my/social-media', [
                'links' => [
                    ['platform' => 'facebook', 'url' => 'not-a-valid-url'],
                ],
            ]);

        $this->assertValidationFailed($response);
    }

    public function test_duplicate_platforms_in_single_request_are_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsApiUser($user)
            ->putJson($this->apiPrefix().'/my/social-media', [
                'links' => [
                    ['platform' => 'facebook', 'url' => 'https://facebook.com/a'],
                    ['platform' => 'facebook', 'url' => 'https://facebook.com/b'],
                ],
            ]);

        $this->assertValidationFailed($response);
    }

    public function test_public_can_show_social_link(): void
    {
        $user = User::factory()->create();
        $link = SocialLink::factory()->forSocialable($user)->platform('facebook')->create();

        $response = $this->getJson($this->apiPrefix().'/social-links/'.$link->id);

        $this->assertApiSuccess($response);
        $response->assertJsonPath('data.id', $link->id);
        $response->assertJsonPath('data.platform', 'facebook');
    }

    public function test_legacy_social_media_show_route_works(): void
    {
        $user = User::factory()->create();
        $link = SocialLink::factory()->forSocialable($user)->create();

        $response = $this->getJson($this->apiPrefix().'/social-media/'.$link->id);

        $this->assertApiSuccess($response);
        $response->assertJsonPath('data.id', $link->id);
    }

    public function test_user_social_links_relationship_returns_owned_rows(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        SocialLink::factory()->forSocialable($user)->count(2)->create();
        SocialLink::factory()->forSocialable($other)->create();

        $this->assertCount(2, $user->fresh()->socialLinks);
        $this->assertTrue(
            $user->socialLinks->every(
                fn (SocialLink $link) => $link->socialable_type === User::class
                    && (int) $link->socialable_id === (int) $user->id
            )
        );
    }
}
