<?php

namespace Tests\Feature\SocialLinks;

use App\Models\Companies;
use App\Models\SocialLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithSocialLinkApi;
use Tests\TestCase;

class AdminSocialLinkTest extends TestCase
{
    use InteractsWithSocialLinkApi;
    use RefreshDatabase;

    public function test_admin_can_list_social_links(): void
    {
        $admin = User::factory()->admin()->create();
        SocialLink::factory()->count(3)->create();

        $response = $this->actingAsApiUser($admin)
            ->getJson($this->adminApiPrefix().'/social-links');

        $this->assertApiSuccess($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data',
            'pagination' => ['current_page', 'last_page', 'per_page', 'total'],
        ]);
        $this->assertGreaterThanOrEqual(3, count($response->json('data')));
    }

    public function test_admin_can_filter_by_socialable_type(): void
    {
        $admin = User::factory()->admin()->create();
        $company = Companies::factory()->create();
        SocialLink::factory()->forSocialable($company)->create();
        SocialLink::factory()->forSocialable(User::factory()->create())->create();

        $response = $this->actingAsApiUser($admin)
            ->getJson($this->adminApiPrefix().'/social-links?'.http_build_query([
                'socialable_type' => Companies::class,
            ]));

        $this->assertApiSuccess($response);
        foreach ($response->json('data') as $row) {
            $this->assertSame(Companies::class, $row['socialable_type']);
        }
    }

    public function test_admin_can_show_social_link(): void
    {
        $admin = User::factory()->admin()->create();
        $link = SocialLink::factory()->create();

        $response = $this->actingAsApiUser($admin)
            ->getJson($this->adminApiPrefix().'/social-links/'.$link->id);

        $this->assertApiSuccess($response);
        $response->assertJsonPath('data.id', $link->id);
    }

    public function test_admin_can_update_social_link(): void
    {
        $admin = User::factory()->admin()->create();
        $link = SocialLink::factory()->platform('facebook')->create();

        $response = $this->actingAsApiUser($admin)
            ->putJson($this->adminApiPrefix().'/social-links/'.$link->id, [
                'url' => 'https://facebook.com/admin-updated',
            ]);

        $this->assertApiSuccess($response);
        $this->assertDatabaseHas('social_links', [
            'id' => $link->id,
            'url' => 'https://facebook.com/admin-updated',
        ]);
    }

    public function test_admin_can_delete_social_link(): void
    {
        $admin = User::factory()->admin()->create();
        $link = SocialLink::factory()->create();

        $response = $this->actingAsApiUser($admin)
            ->deleteJson($this->adminApiPrefix().'/social-links/'.$link->id);

        $this->assertApiSuccess($response);
        $this->assertDatabaseMissing('social_links', ['id' => $link->id]);
    }

    public function test_admin_duplicate_platform_returns_error(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        SocialLink::factory()->forSocialable($user)->platform('instagram')->create();

        $response = $this->actingAsApiUser($admin)
            ->postJson($this->adminApiPrefix().'/social-links', [
                'socialable_type' => User::class,
                'socialable_id' => $user->id,
                'platform' => 'instagram',
                'url' => 'https://instagram.com/duplicate',
            ]);

        $this->assertApiError($response, 422);
    }

    public function test_admin_company_social_links_routes(): void
    {
        $admin = User::factory()->admin()->create();
        $company = Companies::factory()->create();
        $link = SocialLink::factory()->forSocialable($company)->create();

        $this->actingAsApiUser($admin)
            ->getJson($this->adminApiPrefix().'/companies/'.$company->id.'/social-links')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->actingAsApiUser($admin)
            ->putJson($this->adminApiPrefix().'/companies/'.$company->id.'/social-links/'.$link->id, [
                'url' => 'https://linkedin.com/company/admin',
            ])
            ->assertOk();

        $this->actingAsApiUser($admin)
            ->deleteJson($this->adminApiPrefix().'/companies/'.$company->id.'/social-links/'.$link->id)
            ->assertOk();

        $this->assertDatabaseMissing('social_links', ['id' => $link->id]);
    }

    public function test_guest_cannot_access_admin_social_links(): void
    {
        $this->getJson($this->adminApiPrefix().'/social-links')->assertUnauthorized();
    }
}
