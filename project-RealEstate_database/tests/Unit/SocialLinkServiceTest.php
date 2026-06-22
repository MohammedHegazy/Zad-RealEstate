<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\SocialLinkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SocialLinkServiceTest extends TestCase
{
    use RefreshDatabase;

    private SocialLinkService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SocialLinkService::class);
    }

    public function test_sync_legacy_fields_creates_platform_rows(): void
    {
        $user = User::factory()->create();

        $this->service->syncLegacyFields($user, [
            'facebook' => 'https://facebook.com/test',
            'instagram' => 'https://instagram.com/test',
        ]);

        $this->assertDatabaseHas('social_links', [
            'socialable_id' => $user->id,
            'platform' => 'facebook',
        ]);
        $this->assertDatabaseHas('social_links', [
            'socialable_id' => $user->id,
            'platform' => 'instagram',
        ]);
    }

    public function test_assert_platform_available_throws_when_duplicate(): void
    {
        $user = User::factory()->create();
        $user->socialLinks()->create([
            'platform' => 'twitter',
            'url' => 'https://twitter.com/existing',
        ]);

        $this->expectException(ValidationException::class);

        $this->service->assertPlatformAvailable($user, 'twitter');
    }

    public function test_format_collection_returns_sorted_links(): void
    {
        $user = User::factory()->create();
        $user->socialLinks()->create(['platform' => 'instagram', 'url' => 'https://instagram.com/a']);
        $user->socialLinks()->create(['platform' => 'facebook', 'url' => 'https://facebook.com/a']);

        $formatted = $this->service->formatCollection($user->load('socialLinks'));

        $this->assertSame(['facebook', 'instagram'], array_column($formatted, 'platform'));
    }
}
