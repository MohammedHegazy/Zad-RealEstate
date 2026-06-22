<?php

namespace Tests\Feature\SocialLinks;

use App\Models\Companies;
use App\Models\SocialLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithSocialLinkApi;
use Tests\TestCase;

class CompanySocialLinkTest extends TestCase
{
    use InteractsWithSocialLinkApi;
    use RefreshDatabase;

    public function test_owner_can_list_company_social_links(): void
    {
        $owner = User::factory()->create();
        $company = Companies::factory()->forOwner($owner)->create();
        SocialLink::factory()->forSocialable($company)->platform('facebook')->create();

        $response = $this->actingAsApiUser($owner)
            ->getJson($this->apiPrefix().'/my/company/social-links');

        $this->assertApiSuccess($response);
        $response->assertJsonCount(1, 'data');
    }

    public function test_owner_can_create_company_social_link(): void
    {
        $owner = User::factory()->create();
        $company = Companies::factory()->forOwner($owner)->create();

        $response = $this->actingAsApiUser($owner)
            ->postJson($this->apiPrefix().'/my/company/social-links', [
                'platform' => 'instagram',
                'url' => 'https://instagram.com/mycompany',
            ]);

        $this->assertApiSuccess($response, 201);
        $this->assertDatabaseHas('social_links', [
            'socialable_type' => Companies::class,
            'socialable_id' => $company->id,
            'platform' => 'instagram',
        ]);
    }

    public function test_owner_can_update_company_social_link(): void
    {
        $owner = User::factory()->create();
        $company = Companies::factory()->forOwner($owner)->create();
        $link = SocialLink::factory()->forSocialable($company)->platform('facebook')->create();

        $response = $this->actingAsApiUser($owner)
            ->putJson($this->apiPrefix().'/my/company/social-links/'.$link->id, [
                'url' => 'https://facebook.com/updated-page',
            ]);

        $this->assertApiSuccess($response);
        $this->assertDatabaseHas('social_links', [
            'id' => $link->id,
            'url' => 'https://facebook.com/updated-page',
        ]);
    }

    public function test_owner_can_delete_company_social_link(): void
    {
        $owner = User::factory()->create();
        $company = Companies::factory()->forOwner($owner)->create();
        $link = SocialLink::factory()->forSocialable($company)->create();

        $response = $this->actingAsApiUser($owner)
            ->deleteJson($this->apiPrefix().'/my/company/social-links/'.$link->id);

        $this->assertApiSuccess($response);
        $this->assertDatabaseMissing('social_links', ['id' => $link->id]);
    }

    public function test_non_owner_cannot_manage_company_social_links(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $company = Companies::factory()->forOwner($owner)->create();
        $link = SocialLink::factory()->forSocialable($company)->create();

        $this->actingAsApiUser($intruder)
            ->getJson($this->apiPrefix().'/my/company/social-links')
            ->assertStatus(404);

        $this->actingAsApiUser($intruder)
            ->postJson($this->apiPrefix().'/my/company/social-links', [
                'platform' => 'twitter',
                'url' => 'https://twitter.com/x',
            ])
            ->assertStatus(404);

        $this->actingAsApiUser($intruder)
            ->putJson($this->apiPrefix().'/my/company/social-links/'.$link->id, [
                'url' => 'https://facebook.com/hacked',
            ])
            ->assertStatus(404);

        $this->actingAsApiUser($intruder)
            ->deleteJson($this->apiPrefix().'/my/company/social-links/'.$link->id)
            ->assertStatus(404);
    }

    public function test_duplicate_platform_per_company_is_rejected(): void
    {
        $owner = User::factory()->create();
        $company = Companies::factory()->forOwner($owner)->create();
        SocialLink::factory()->forSocialable($company)->platform('facebook')->create();

        $response = $this->actingAsApiUser($owner)
            ->postJson($this->apiPrefix().'/my/company/social-links', [
                'platform' => 'facebook',
                'url' => 'https://facebook.com/another',
            ]);

        $this->assertValidationFailed($response);
    }

    public function test_invalid_url_on_create_is_rejected(): void
    {
        $owner = User::factory()->create();
        Companies::factory()->forOwner($owner)->create();

        $response = $this->actingAsApiUser($owner)
            ->postJson($this->apiPrefix().'/my/company/social-links', [
                'platform' => 'website',
                'url' => 'invalid-url',
            ]);

        $this->assertValidationFailed($response);
    }

    public function test_cannot_update_link_from_another_companys_profile(): void
    {
        $owner = User::factory()->create();
        $otherOwner = User::factory()->create();
        Companies::factory()->forOwner($owner)->create();
        $otherCompany = Companies::factory()->forOwner($otherOwner)->create();
        $linkOnOther = SocialLink::factory()->forSocialable($otherCompany)->create();

        $this->actingAsApiUser($owner)
            ->putJson($this->apiPrefix().'/my/company/social-links/'.$linkOnOther->id, [
                'url' => 'https://facebook.com/wrong',
            ])
            ->assertStatus(404);
    }

    public function test_company_social_links_relationship(): void
    {
        $company = Companies::factory()->create();
        SocialLink::factory()->forSocialable($company)->count(3)->create();

        $this->assertCount(3, $company->fresh()->socialLinks);
    }
}
