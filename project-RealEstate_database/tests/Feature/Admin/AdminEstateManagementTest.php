<?php

namespace Tests\Feature\Admin;

use App\Models\Estate;
use App\Models\Places;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\InteractsWithTrustApi;
use Tests\TestCase;

class AdminEstateManagementTest extends TestCase
{
    use InteractsWithTrustApi;
    use RefreshDatabase;

    public function test_admin_can_list_estates_with_filters(): void
    {
        $admin = User::factory()->admin()->create();
        Estate::factory()->active()->create(['name' => 'Villa Alpha']);
        Estate::factory()->pending()->create(['name' => 'Office Beta']);

        $response = $this->actingAsApiUser($admin)
            ->getJson($this->adminApiPrefix().'/estates?'.http_build_query([
                'search' => 'Villa',
                'status' => 'active',
            ]));

        $this->assertApiSuccess($response);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertSame('Villa Alpha', $data[0]['name']);
    }

    public function test_admin_can_view_estate_details(): void
    {
        $admin = User::factory()->admin()->create();
        $estate = Estate::factory()->create();

        $response = $this->actingAsApiUser($admin)
            ->getJson($this->adminApiPrefix().'/estates/'.$estate->id);

        $this->assertApiSuccess($response);
        $response->assertJsonPath('data.id', $estate->id);
        $response->assertJsonPath('data.name', $estate->name);
    }

    public function test_admin_can_create_estate_for_owner(): void
    {
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->owner()->create();
        $place = Places::factory()->create();

        $payload = [
            'user_id' => $owner->id,
            'places_id' => $place->id,
            'latitude' => 33.5,
            'longitude' => 36.3,
            'name' => 'Admin Created Estate',
            'phone' => '991234567',
            'country_code_phone' => '+963',
            'space_of_estate' => 120,
            'price_of_meter' => 1500,
            'price' => 180000,
            'type_text' => 'residential',
            'kind_text' => 'apartment',
            'description' => 'Created by admin panel.',
            'status' => 'pending',
        ];

        $response = $this->actingAsApiUser($admin)
            ->postJson($this->adminApiPrefix().'/estates', $payload);

        $this->assertApiSuccess($response, 201);
        $this->assertDatabaseHas('estates', [
            'user_id' => $owner->id,
            'name' => 'Admin Created Estate',
            'status' => 'pending',
        ]);
    }

    public function test_admin_can_create_estate_with_images_and_videos(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $owner = User::factory()->owner()->create();
        $place = Places::factory()->create();

        $response = $this->actingAsApiUser($admin)
            ->post($this->adminApiPrefix().'/estates', [
                'user_id' => $owner->id,
                'places_id' => $place->id,
                'latitude' => 33.5,
                'longitude' => 36.3,
                'name' => 'Estate With Media',
                'phone' => '991234567',
                'country_code_phone' => '+963',
                'space_of_estate' => 120,
                'price_of_meter' => 1500,
                'price' => 180000,
                'type_text' => 'residential',
                'kind_text' => 'apartment',
                'description' => 'With gallery media.',
                'status' => 'pending',
                'images' => [
                    UploadedFile::fake()->create('photo-a.jpg', 10, 'image/jpeg'),
                    UploadedFile::fake()->create('photo-b.jpg', 10, 'image/jpeg'),
                ],
                'primary_image_index' => 1,
                'videos' => [
                    UploadedFile::fake()->create('tour.mp4', 100, 'video/mp4'),
                ],
                'ads' => [
                    UploadedFile::fake()->create('ad.jpg', 10, 'image/jpeg'),
                ],
                'main_ad_index' => 0,
            ]);

        $this->assertApiSuccess($response, 201);
        $estateId = $response->json('data.id');

        $this->assertDatabaseCount('estate_images', 2);
        $this->assertDatabaseCount('estate_videos', 1);
        $this->assertDatabaseCount('estate_ads', 1);
        $this->assertDatabaseHas('estate_images', [
            'estate_id' => $estateId,
            'is_primary' => true,
        ]);
        $this->assertDatabaseHas('estate_ads', [
            'estate_id' => $estateId,
            'is_main' => true,
        ]);

        $images = $response->json('data.images');
        $this->assertCount(2, $images);
        $this->assertNotNull($images[0]['image_url']);
        $this->assertNotNull($images[1]['image_url']);
    }

    public function test_admin_can_upload_estate_image_with_url_in_response(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $estate = Estate::factory()->create();

        $response = $this->actingAsApiUser($admin)
            ->post($this->adminApiPrefix().'/estates/'.$estate->id.'/images', [
                'image' => UploadedFile::fake()->create('gallery.jpg', 10, 'image/jpeg'),
                'is_primary' => true,
            ]);

        $this->assertApiSuccess($response, 201);
        $this->assertNotNull($response->json('data.image_url'));
        $this->assertStringContainsString('/storage/estates/', $response->json('data.image_url'));

        $show = $this->actingAsApiUser($admin)
            ->getJson($this->adminApiPrefix().'/estates/'.$estate->id);

        $this->assertApiSuccess($show);
        $this->assertNotNull($show->json('data.images.0.image_url'));
    }

    public function test_admin_can_update_estate_fields_and_status(): void
    {
        $admin = User::factory()->admin()->create();
        $estate = Estate::factory()->pending()->create(['name' => 'Old Name']);

        $response = $this->actingAsApiUser($admin)
            ->putJson($this->adminApiPrefix().'/estates/'.$estate->id, [
                'name' => 'Updated Estate Name',
                'status' => 'active',
            ]);

        $this->assertApiSuccess($response);
        $this->assertDatabaseHas('estates', [
            'id' => $estate->id,
            'name' => 'Updated Estate Name',
            'status' => 'active',
        ]);
    }

    public function test_admin_can_update_estate_status_via_patch(): void
    {
        $admin = User::factory()->admin()->create();
        $estate = Estate::factory()->pending()->create();

        $response = $this->actingAsApiUser($admin)
            ->patchJson($this->adminApiPrefix().'/estates/'.$estate->id.'/status', [
                'status' => 'rejected',
            ]);

        $this->assertApiSuccess($response);
        $this->assertDatabaseHas('estates', [
            'id' => $estate->id,
            'status' => 'rejected',
        ]);
    }

    public function test_admin_can_delete_estate(): void
    {
        $admin = User::factory()->admin()->create();
        $estate = Estate::factory()->create();

        $response = $this->actingAsApiUser($admin)
            ->deleteJson($this->adminApiPrefix().'/estates/'.$estate->id);

        $this->assertApiSuccess($response);
        $this->assertDatabaseMissing('estates', ['id' => $estate->id]);
    }
}
