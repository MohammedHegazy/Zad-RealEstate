<?php

namespace Tests\Feature\Admin;

use App\Models\Cities;
use App\Models\Companies;
use App\Models\Estate;
use App\Models\Places;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\InteractsWithTrustApi;
use Tests\TestCase;

class AdminLocationManagementTest extends TestCase
{
    use InteractsWithTrustApi;
    use RefreshDatabase;

    public function test_admin_can_list_cities_with_search(): void
    {
        $admin = User::factory()->admin()->create();
        Cities::factory()->create(['name' => 'Damascus']);
        Cities::factory()->create(['name' => 'Aleppo']);

        $response = $this->actingAsApiUser($admin)
            ->getJson($this->adminApiPrefix().'/cities?'.http_build_query(['search' => 'Dam']));

        $this->assertApiSuccess($response);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertSame('Damascus', $data[0]['name']);
    }

    public function test_admin_can_create_and_update_city(): void
    {
        $admin = User::factory()->admin()->create();

        $create = $this->actingAsApiUser($admin)
            ->postJson($this->adminApiPrefix().'/cities', [
                'name' => 'Homs',
                'latitude' => 34.73,
                'longitude' => 36.72,
            ]);

        $this->assertApiSuccess($create, 201);
        $cityId = $create->json('data.id');

        $update = $this->actingAsApiUser($admin)
            ->putJson($this->adminApiPrefix().'/cities/'.$cityId, [
                'name' => 'Homs City',
            ]);

        $this->assertApiSuccess($update);
        $this->assertDatabaseHas('cities', [
            'id' => $cityId,
            'name' => 'Homs City',
        ]);
    }

    public function test_admin_can_update_city_image_via_post(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $city = Cities::factory()->create(['name' => 'Latakia']);

        $response = $this->actingAsApiUser($admin)
            ->post($this->adminApiPrefix().'/cities/'.$city->id, [
                'name' => 'Latakia City',
                'image' => UploadedFile::fake()->create('city.jpg', 10, 'image/jpeg'),
            ]);

        $this->assertApiSuccess($response);
        $this->assertDatabaseHas('cities', [
            'id' => $city->id,
            'name' => 'Latakia City',
        ]);
        $this->assertNotNull($city->fresh()->image);
        $this->assertNotNull($response->json('data.image_url'));
        $this->assertStringContainsString('/storage/cities/', $response->json('data.image_url'));
    }

    public function test_admin_cannot_delete_city_with_places(): void
    {
        $admin = User::factory()->admin()->create();
        $city = Cities::factory()->create();
        Places::factory()->create(['cities_id' => $city->id]);

        $response = $this->actingAsApiUser($admin)
            ->deleteJson($this->adminApiPrefix().'/cities/'.$city->id);

        $response->assertStatus(422);
        $this->assertDatabaseHas('cities', ['id' => $city->id]);
    }

    public function test_admin_can_list_places_with_city_filter(): void
    {
        $admin = User::factory()->admin()->create();
        $city = Cities::factory()->create();
        Places::factory()->create(['cities_id' => $city->id, 'name' => 'Mazzeh']);
        Places::factory()->create();

        $response = $this->actingAsApiUser($admin)
            ->getJson($this->adminApiPrefix().'/places?'.http_build_query([
                'cities_id' => $city->id,
            ]));

        $this->assertApiSuccess($response);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertSame('Mazzeh', $data[0]['name']);
    }

    public function test_admin_can_create_and_update_place(): void
    {
        $admin = User::factory()->admin()->create();
        $city = Cities::factory()->create();

        $create = $this->actingAsApiUser($admin)
            ->postJson($this->adminApiPrefix().'/places', [
                'cities_id' => $city->id,
                'name' => 'Al-Shaar',
                'latitude' => 33.51,
                'longitude' => 36.28,
            ]);

        $this->assertApiSuccess($create, 201);
        $placeId = $create->json('data.id');

        $update = $this->actingAsApiUser($admin)
            ->putJson($this->adminApiPrefix().'/places/'.$placeId, [
                'name' => 'Al-Shaar District',
            ]);

        $this->assertApiSuccess($update);
        $this->assertDatabaseHas('places', [
            'id' => $placeId,
            'name' => 'Al-Shaar District',
        ]);
    }

    public function test_admin_cannot_delete_place_with_estates_or_companies(): void
    {
        $admin = User::factory()->admin()->create();
        $place = Places::factory()->create();
        Estate::factory()->create(['places_id' => $place->id]);

        $response = $this->actingAsApiUser($admin)
            ->deleteJson($this->adminApiPrefix().'/places/'.$place->id);

        $response->assertStatus(422);
        $this->assertDatabaseHas('places', ['id' => $place->id]);

        $emptyPlace = Places::factory()->create();
        Companies::factory()->create(['places_id' => $emptyPlace->id]);

        $response = $this->actingAsApiUser($admin)
            ->deleteJson($this->adminApiPrefix().'/places/'.$emptyPlace->id);

        $response->assertStatus(422);
    }

    public function test_admin_can_delete_place_without_dependencies(): void
    {
        $admin = User::factory()->admin()->create();
        $place = Places::factory()->create();

        $response = $this->actingAsApiUser($admin)
            ->deleteJson($this->adminApiPrefix().'/places/'.$place->id);

        $this->assertApiSuccess($response);
        $this->assertDatabaseMissing('places', ['id' => $place->id]);
    }
}
