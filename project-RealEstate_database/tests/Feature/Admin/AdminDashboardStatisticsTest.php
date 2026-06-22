<?php

namespace Tests\Feature\Admin;

use App\Models\Estate;
use App\Models\PropertyReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTrustApi;
use Tests\TestCase;

class AdminDashboardStatisticsTest extends TestCase
{
    use InteractsWithTrustApi;
    use RefreshDatabase;

    public function test_admin_can_fetch_dashboard_statistics(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->count(2)->create();
        Estate::factory()->count(2)->create(['status' => 'pending']);
        PropertyReview::factory()->count(1)->create(['status' => 'pending']);

        $response = $this->actingAsApiUser($admin)
            ->getJson($this->adminApiPrefix().'/statistics');

        $this->assertApiSuccess($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'totals' => [
                    'users',
                    'agents',
                    'estates',
                    'companies',
                    'cities',
                    'places',
                ],
                'moderation' => [
                    'estates_pending',
                    'estates_active',
                    'estates_rejected',
                    'companies_pending',
                    'reviews_pending' => ['property', 'agent', 'company', 'total'],
                    'verifications_pending',
                ],
                'estates_by_status',
                'companies_by_status',
                'users_by_type',
                'users_by_status',
                'registrations' => [
                    'users_last_7_days',
                    'estates_last_7_days',
                ],
                'recent_users',
                'recent_estates',
            ],
        ]);

        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(3, $data['totals']['users']);
        $this->assertGreaterThanOrEqual(2, $data['moderation']['estates_pending']);
        $this->assertGreaterThanOrEqual(1, $data['moderation']['reviews_pending']['property']);
        $this->assertCount(7, $data['registrations']['users_last_7_days']);
    }

    public function test_non_admin_cannot_fetch_dashboard_statistics(): void
    {
        $user = User::factory()->create(['type' => 'buyer']);

        $response = $this->actingAsApiUser($user)
            ->getJson($this->adminApiPrefix().'/statistics');

        $response->assertForbidden();
    }
}
