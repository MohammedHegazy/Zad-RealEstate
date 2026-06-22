<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Geo\EstateInRadiusRequest;
use App\Http\Requests\Geo\EstateNearbyRequest;
use App\Http\Requests\Geo\EstateMapRequest;
use App\Models\Estate;
use App\Services\GeoSearchService;
use Illuminate\Http\JsonResponse;

class EstateGeoController extends BaseApiController
{
    public function __construct(
        private readonly GeoSearchService $geo,
    ) {}

    public function nearby(EstateNearbyRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $estates = $this->geo->searchNearby(
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            (int) ($validated['limit'] ?? 10),
            isset($validated['radius_km']) ? (float) $validated['radius_km'] : null,
        );

        return $this->successResponse(
            [
                'origin' => [
                    'latitude' => (float) $validated['latitude'],
                    'longitude' => (float) $validated['longitude'],
                ],
                'estates' => $estates->map(fn (Estate $estate) => $this->formatGeoEstate($estate))->values()->all(),
            ],
            'Nearby estates retrieved.'
        );
    }

    public function inRadius(EstateInRadiusRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $paginator = $this->geo->searchWithinRadius(
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            (float) $validated['radius_km'],
            (int) ($validated['per_page'] ?? 15),
            $request->only(['type_text', 'kind_text', 'min_price', 'max_price']),
            (int) $request->integer('page', 1),
        );

        return $this->successResponse(
            [
                'origin' => [
                    'latitude' => (float) $validated['latitude'],
                    'longitude' => (float) $validated['longitude'],
                    'radius_km' => (float) $validated['radius_km'],
                ],
                'estates' => collect($paginator->items())
                    ->map(fn (Estate $estate) => $this->formatGeoEstate($estate))
                    ->values()
                    ->all(),
            ],
            'Estates within radius retrieved.',
            200,
            $this->paginationMeta($paginator),
        );
    }

    public function map(EstateMapRequest $request): JsonResponse
    {
        $bounds = null;

        if ($request->filled('north')) {
            $bounds = $request->only(['north', 'south', 'east', 'west']);
        }

        return $this->successResponse(
            $this->geo->getMapData($bounds),
            'Map data retrieved.'
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function formatGeoEstate(Estate $estate): array
    {
        return [
            'id' => $estate->id,
            'name' => $estate->name,
            'price' => $estate->price,
            'latitude' => $estate->latitude,
            'longitude' => $estate->longitude,
            'type_text' => $estate->type_text,
            'kind_text' => $estate->kind_text,
            'distance_km' => isset($estate->distance_km) ? round((float) $estate->distance_km, 3) : null,
            'place' => $estate->relationLoaded('place') && $estate->place
                ? [
                    'id' => $estate->place->id,
                    'name' => $estate->place->name,
                    'latitude' => $estate->place->latitude,
                    'longitude' => $estate->place->longitude,
                    'city' => $estate->place->relationLoaded('city') && $estate->place->city
                        ? $estate->place->city->only(['id', 'name', 'latitude', 'longitude'])
                        : null,
                ]
                : null,
        ];
    }
}
