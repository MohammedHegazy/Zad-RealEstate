<?php

namespace Tests\Feature\Geo;

use App\Models\Estate;
use App\Models\Places;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstateGeoTest extends TestCase
{
    use RefreshDatabase;

    private const ORIGIN_LAT = 33.5138;

    private const ORIGIN_LNG = 36.2765;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedGeoEstates();
    }

    public function test_nearby_returns_estates_ordered_by_distance(): void
    {
        $response = $this->getJson('/api/v1/estates/nearby?'.http_build_query([
            'latitude' => self::ORIGIN_LAT,
            'longitude' => self::ORIGIN_LNG,
            'limit' => 5,
            'radius_km' => 10,
        ]));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'origin' => ['latitude', 'longitude'],
                    'estates' => [
                        '*' => ['id', 'latitude', 'longitude', 'distance_km'],
                    ],
                ],
            ]);

        $estates = $response->json('data.estates');
        $this->assertNotEmpty($estates);
        $this->assertLessThanOrEqual(5, count($estates));

        if (count($estates) >= 2) {
            $this->assertLessThanOrEqual(
                $estates[1]['distance_km'],
                $estates[0]['distance_km'],
            );
        }
    }

    public function test_in_radius_filters_by_distance_and_paginates(): void
    {
        $response = $this->getJson('/api/v1/estates/in-radius?'.http_build_query([
            'latitude' => self::ORIGIN_LAT,
            'longitude' => self::ORIGIN_LNG,
            'radius_km' => 2,
            'per_page' => 10,
        ]));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'origin' => ['latitude', 'longitude', 'radius_km'],
                    'estates',
                ],
                'pagination' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);

        foreach ($response->json('data.estates') as $estate) {
            $this->assertLessThanOrEqual(2.0, $estate['distance_km']);
        }
    }

    public function test_map_returns_markers_and_provider_config(): void
    {
        $response = $this->getJson('/api/v1/estates/map');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'providers' => [
                        'leaflet' => ['tile_url', 'attribution'],
                        'openstreetmap' => ['tile_url', 'attribution'],
                        'google_maps' => ['requires_api_key', 'api_key_env'],
                    ],
                    'center' => ['latitude', 'longitude'],
                    'default_zoom',
                    'markers',
                    'total_markers',
                ],
            ]);

        $this->assertGreaterThan(0, $response->json('data.total_markers'));
    }

    public function test_map_filters_by_bounding_box(): void
    {
        $response = $this->getJson('/api/v1/estates/map?'.http_build_query([
            'north' => 33.52,
            'south' => 33.51,
            'east' => 36.28,
            'west' => 36.27,
        ]));

        $response->assertOk();

        foreach ($response->json('data.markers') as $marker) {
            $this->assertGreaterThanOrEqual(33.51, (float) $marker['latitude']);
            $this->assertLessThanOrEqual(33.52, (float) $marker['latitude']);
            $this->assertGreaterThanOrEqual(36.27, (float) $marker['longitude']);
            $this->assertLessThanOrEqual(36.28, (float) $marker['longitude']);
        }
    }

    public function test_nearby_requires_coordinates(): void
    {
        $this->getJson('/api/v1/estates/nearby')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['latitude', 'longitude']);
    }

    private function seedGeoEstates(): void
    {
        $owner = User::factory()->create();
        $place = Places::factory()->create([
            'latitude' => self::ORIGIN_LAT,
            'longitude' => self::ORIGIN_LNG,
        ]);

        Estate::factory()->forOwner($owner)->withCoordinates(33.5140, 36.2770)->create([
            'places_id' => $place->id,
            'name' => 'Near Estate',
            'status' => 'active',
        ]);

        Estate::factory()->forOwner($owner)->withCoordinates(33.5200, 36.2800)->create([
            'places_id' => $place->id,
            'name' => 'Mid Estate',
            'status' => 'active',
        ]);

        Estate::factory()->forOwner($owner)->withCoordinates(33.6000, 36.4000)->create([
            'places_id' => $place->id,
            'name' => 'Far Estate',
            'status' => 'active',
        ]);

        Estate::factory()->forOwner($owner)->create([
            'places_id' => $place->id,
            'name' => 'No Coordinates',
            'status' => 'active',
            'latitude' => null,
            'longitude' => null,
        ]);
    }
}
