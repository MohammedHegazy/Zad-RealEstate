<?php

namespace Tests\Feature\Notifications;

use App\Models\Notifications;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTrustApi;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use InteractsWithTrustApi;
    use RefreshDatabase;

    public function test_user_can_list_own_notifications(): void
    {
        $user = User::factory()->create();
        Notifications::factory()->count(2)->create(['user_id' => $user->id]);
        Notifications::factory()->create();

        $response = $this->actingAsApiUser($user)
            ->getJson($this->apiPrefix().'/notifications');

        $this->assertApiSuccess($response);
        $this->assertCount(2, $response->json('data'));
    }

    public function test_user_can_filter_notifications_by_read_status(): void
    {
        $user = User::factory()->create();
        Notifications::factory()->create(['user_id' => $user->id, 'is_read' => false]);
        Notifications::factory()->read()->create(['user_id' => $user->id]);

        $response = $this->actingAsApiUser($user)
            ->getJson($this->apiPrefix().'/notifications?is_read=0');

        $this->assertApiSuccess($response);
        $this->assertCount(1, $response->json('data'));
        $this->assertFalse($response->json('data.0.is_read'));
    }

    public function test_user_cannot_access_another_users_notification(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $notification = Notifications::factory()->create(['user_id' => $other->id]);

        $this->actingAsApiUser($user)
            ->getJson($this->apiPrefix().'/notifications/'.$notification->id)
            ->assertStatus(404);
    }

    public function test_show_marks_unread_notification_as_read(): void
    {
        $user = User::factory()->create();
        $notification = Notifications::factory()->create([
            'user_id' => $user->id,
            'is_read' => false,
        ]);

        $response = $this->actingAsApiUser($user)
            ->getJson($this->apiPrefix().'/notifications/'.$notification->id);

        $this->assertApiSuccess($response);
        $this->assertTrue($response->json('data.is_read'));
        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'is_read' => true,
        ]);
    }

    public function test_user_can_mark_notification_as_read(): void
    {
        $user = User::factory()->create();
        $notification = Notifications::factory()->create([
            'user_id' => $user->id,
            'is_read' => false,
        ]);

        $response = $this->actingAsApiUser($user)
            ->patchJson($this->apiPrefix().'/notifications/'.$notification->id.'/read');

        $this->assertApiSuccess($response);
        $this->assertTrue($response->json('data.is_read'));
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $user = User::factory()->create();
        Notifications::factory()->count(3)->create(['user_id' => $user->id, 'is_read' => false]);

        $response = $this->actingAsApiUser($user)
            ->patchJson($this->apiPrefix().'/notifications/read-all');

        $this->assertApiSuccess($response);
        $this->assertSame(3, $response->json('data.updated'));
        $this->assertSame(0, Notifications::query()->where('user_id', $user->id)->where('is_read', false)->count());
    }

    public function test_unread_count_endpoint(): void
    {
        $user = User::factory()->create();
        Notifications::factory()->count(2)->create(['user_id' => $user->id, 'is_read' => false]);
        Notifications::factory()->read()->create(['user_id' => $user->id]);

        $response = $this->actingAsApiUser($user)
            ->getJson($this->apiPrefix().'/notifications/unread-count');

        $this->assertApiSuccess($response);
        $this->assertSame(2, $response->json('data.unread_count'));
    }

    public function test_user_can_delete_own_notification(): void
    {
        $user = User::factory()->create();
        $notification = Notifications::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAsApiUser($user)
            ->deleteJson($this->apiPrefix().'/notifications/'.$notification->id);

        $this->assertApiSuccess($response);
        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }

    public function test_admin_can_send_notification_to_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $response = $this->actingAsApiUser($admin)->postJson(
            $this->adminApiPrefix().'/notifications',
            [
                'user_id' => $user->id,
                'content' => 'Your estate listing was approved.',
            ]
        );

        $this->assertApiSuccess($response, 201);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'content' => 'Your estate listing was approved.',
            'is_read' => false,
        ]);
    }

    public function test_notification_service_send_to_many(): void
    {
        $users = User::factory()->count(3)->create();
        $service = app(NotificationService::class);

        $inserted = $service->sendToMany($users->pluck('id')->all(), 'Broadcast message');

        $this->assertSame(3, $inserted);
        $this->assertDatabaseCount('notifications', 3);
    }

    public function test_notifications_are_deleted_when_user_is_deleted(): void
    {
        $user = User::factory()->create();
        Notifications::factory()->count(2)->create(['user_id' => $user->id]);

        $user->delete();

        $this->assertDatabaseCount('notifications', 0);
    }
}
