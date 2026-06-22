<?php

namespace Tests\Unit\Services;

use App\Services\GeoSearchService;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GeoSearchServiceTest extends TestCase
{
    private GeoSearchService $geo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->geo = app(GeoSearchService::class);
    }

    #[DataProvider('distanceProvider')]
    public function test_calculate_distance_km(float $lat1, float $lng1, float $lat2, float $lng2, float $expectedKm): void
    {
        $distance = $this->geo->calculateDistanceKm($lat1, $lng1, $lat2, $lng2);

        $this->assertEqualsWithDelta($expectedKm, $distance, 0.5);
    }

    public static function distanceProvider(): array
    {
        return [
            'same point' => [33.5138, 36.2765, 33.5138, 36.2765, 0.0],
            'roughly one km north' => [33.5138, 36.2765, 33.5228, 36.2765, 1.0],
        ];
    }

    public function test_map_providers_include_leaflet_osm_and_google(): void
    {
        $providers = $this->geo->mapProviders();

        $this->assertArrayHasKey('leaflet', $providers);
        $this->assertArrayHasKey('openstreetmap', $providers);
        $this->assertArrayHasKey('google_maps', $providers);
        $this->assertSame('google_maps', $providers['google_maps']['library']);
    }
}
