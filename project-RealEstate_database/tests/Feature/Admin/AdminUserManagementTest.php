<?php

namespace Tests\Feature\Admin;

use App\Models\Estate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTrustApi;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use InteractsWithTrustApi;
    use RefreshDatabase;

    public function test_admin_can_list_users_with_filters(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->buyer()->create(['fname' => 'Ahmad', 'status' => 'active']);
        User::factory()->owner()->create(['status' => 'suspended']);

        $response = $this->actingAsApiUser($admin)
            ->getJson($this->adminApiPrefix().'/users?'.http_build_query([
                'search' => 'Ahmad',
                'status' => 'active',
                'per_page' => 10,
            ]));

        $this->assertApiSuccess($response);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertSame('Ahmad', $data[0]['fname']);
        $this->assertArrayHasKey('estates_count', $data[0]);
    }

    public function test_admin_can_view_user_details(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->owner()->create();
        Estate::factory()->count(2)->create(['user_id' => $user->id]);

        $response = $this->actingAsApiUser($admin)
            ->getJson($this->adminApiPrefix().'/users/'.$user->id);

        $this->assertApiSuccess($response);
        $response->assertJsonPath('data.id', $user->id);
        $response->assertJsonPath('data.counts.estates', 2);
    }

    public function test_admin_can_update_user_status_and_verification(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->buyer()->create(['status' => 'active', 'is_verified' => false]);

        $response = $this->actingAsApiUser($admin)
            ->putJson($this->adminApiPrefix().'/users/'.$user->id, [
                'status' => 'suspended',
                'is_verified' => true,
            ]);

        $this->assertApiSuccess($response);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => 'suspended',
            'is_verified' => 1,
        ]);
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAsApiUser($admin)
            ->deleteJson($this->adminApiPrefix().'/users/'.$admin->id);

        $response->assertStatus(422);
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_can_delete_non_admin_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->buyer()->create();

        $response = $this->actingAsApiUser($admin)
            ->deleteJson($this->adminApiPrefix().'/users/'.$user->id);

        $this->assertApiSuccess($response, 200);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
