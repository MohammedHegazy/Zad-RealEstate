<?php

namespace App\Services;

use App\Models\Estate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;

class GeoSearchService
{
    private const EARTH_RADIUS_KM = 6371;

    /**
     * Haversine distance in kilometres between two WGS-84 points.
     */
    public function calculateDistanceKm(
        float $lat1,
        float $lng1,
        float $lat2,
        float $lng2,
    ): float {
        $latFrom = deg2rad($lat1);
        $latTo = deg2rad($lat2);
        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $a = sin($latDelta / 2) ** 2
            + cos($latFrom) * cos($latTo) * sin($lngDelta / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round(self::EARTH_RADIUS_KM * $c, 3);
    }

    /**
     * Find the nearest active estates to a point.
     *
     * @return Collection<int, Estate>
     */
    public function searchNearby(
        float $latitude,
        float $longitude,
        int $limit = 10,
        ?float $maxRadiusKm = null,
    ): Collection {
        $maxRadiusKm ??= (float) config('realestate.geo.default_nearby_radius_km', 25);

        return $this->rankByDistance(
            $this->candidateQuery($latitude, $longitude, $maxRadiusKm)->get(),
            $latitude,
            $longitude,
        )
            ->filter(fn (Estate $estate) => $estate->distance_km <= $maxRadiusKm)
            ->take($limit)
            ->values();
    }

    /**
     * Paginated estates within a radius (km).
     *
     * @param  array<string, mixed>  $filters
     */
    public function searchWithinRadius(
        float $latitude,
        float $longitude,
        float $radiusKm,
        int $perPage = 15,
        array $filters = [],
        int $page = 1,
    ): LengthAwarePaginator {
        $maxRadius = (float) config('realestate.geo.max_radius_km', 100);
        $radiusKm = min($radiusKm, $maxRadius);

        $query = $this->candidateQuery($latitude, $longitude, $radiusKm);
        $this->applyFilters($query, $filters);

        $ranked = $this->rankByDistance($query->get(), $latitude, $longitude)
            ->filter(fn (Estate $estate) => $estate->distance_km <= $radiusKm)
            ->values();

        $total = $ranked->count();
        $items = $ranked->slice(($page - 1) * $perPage, $perPage)->values()->all();

        return new Paginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath()],
        );
    }

    /**
     * Map payload for Leaflet / OpenStreetMap / Google Maps clients.
     *
     * @param  array<string, float>|null  $bounds  north, south, east, west
     * @return array<string, mixed>
     */
    public function getMapData(?array $bounds = null): array
    {
        $query = Estate::query()
            ->where('status', 'active')
            ->withCoordinates()
            ->with(['place.city']);

        if ($bounds !== null) {
            $query->withinMapBounds(
                (float) $bounds['north'],
                (float) $bounds['south'],
                (float) $bounds['east'],
                (float) $bounds['west'],
            );
        }

        $estates = $query
            ->orderBy('id')
            ->get(['id', 'name', 'price', 'latitude', 'longitude', 'type_text', 'kind_text', 'places_id']);

        return [
            'providers' => $this->mapProviders(),
            'center' => [
                'latitude' => config('realestate.map.default_lat'),
                'longitude' => config('realestate.map.default_lng'),
            ],
            'default_zoom' => config('realestate.map.default_zoom'),
            'markers' => $estates->map(fn (Estate $estate) => $this->formatMarker($estate))->values()->all(),
            'total_markers' => $estates->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function mapProviders(): array
    {
        return [
            'leaflet' => [
                'library' => 'leaflet',
                'tile_url' => config('realestate.map.tile_url'),
                'attribution' => config('realestate.map.attribution'),
            ],
            'openstreetmap' => [
                'library' => 'leaflet',
                'tile_url' => config('realestate.geo.osm_tile_url', config('realestate.map.tile_url')),
                'attribution' => config('realestate.geo.osm_attribution', config('realestate.map.attribution')),
            ],
            'google_maps' => [
                'library' => 'google_maps',
                'requires_api_key' => true,
                'api_key_configured' => filled(config('realestate.geo.google_maps_api_key')),
                'api_key_env' => 'GOOGLE_MAPS_API_KEY',
            ],
        ];
    }

    /**
     * @param  Builder<Estate>  $query
     * @param  array<string, mixed>  $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['type_text'])) {
            $query->where('type_text', $filters['type_text']);
        }

        if (! empty($filters['kind_text'])) {
            $query->where('kind_text', $filters['kind_text']);
        }

        if (isset($filters['min_price'])) {
            $query->where('price', '>=', (float) $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $query->where('price', '<=', (float) $filters['max_price']);
        }
    }

    /** @return Builder<Estate> */
    private function candidateQuery(float $latitude, float $longitude, float $radiusKm): Builder
    {
        [$minLat, $maxLat, $minLng, $maxLng] = $this->boundingBox($latitude, $longitude, $radiusKm);

        return Estate::query()
            ->where('status', 'active')
            ->withCoordinates()
            ->whereBetween('latitude', [$minLat, $maxLat])
            ->whereBetween('longitude', [$minLng, $maxLng])
            ->with(['place.city']);
    }

    /**
     * Approximate bounding box for a radius in km (pre-filter before Haversine).
     *
     * @return array{0: float, 1: float, 2: float, 3: float}
     * تحسب المسافة. "هات فقط العقارات القريبة تقريباً" لتقليل العمليات الحسابية.
     */
    private function boundingBox(float $latitude, float $longitude, float $radiusKm): array
    {
        $latDelta = $radiusKm / 111.045;
        $lngDelta = $radiusKm / (111.045 * max(cos(deg2rad($latitude)), 0.00001));

        return [
            $latitude - $latDelta,
            $latitude + $latDelta,
            $longitude - $lngDelta,
            $longitude + $lngDelta,
        ];
    }

    /**
     * @param  Collection<int, Estate>  $estates
     * @return Collection<int, Estate>
     */
    private function rankByDistance(Collection $estates, float $latitude, float $longitude): Collection
    {
        return $estates
            ->each(function (Estate $estate) use ($latitude, $longitude) {
                $estate->distance_km = $this->calculateDistanceKm(
                    $latitude,
                    $longitude,
                    (float) $estate->latitude,
                    (float) $estate->longitude,
                );
            })
            ->sortBy('distance_km')
            ->values();
    }

    /**
     * @return array<string, mixed>
     * تحويل Estate إلى بيانات مناسبة للخريطة.
     */
    private function formatMarker(Estate $estate): array
    {
        return [
            'id' => $estate->id,
            'name' => $estate->name,
            'price' => $estate->price,
            'latitude' => $estate->latitude,
            'longitude' => $estate->longitude,
            'type_text' => $estate->type_text,
            'kind_text' => $estate->kind_text,
            'place' => $estate->relationLoaded('place') && $estate->place
                ? [
                    'id' => $estate->place->id,
                    'name' => $estate->place->name,
                    'city' => $estate->place->relationLoaded('city') && $estate->place->city
                        ? $estate->place->city->only(['id', 'name'])
                        : null,
                ]
                : null,
        ];
    }
}
