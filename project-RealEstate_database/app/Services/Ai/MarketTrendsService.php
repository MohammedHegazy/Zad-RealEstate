<?php

namespace App\Services\Ai;

use App\Models\Estate;
use App\Models\PricePrediction;
use Illuminate\Support\Facades\DB;

class MarketTrendsService
{
    /**
     * Aggregated listing and prediction trends (no extra ML — SQL analytics).
     *
     * @return array<string, mixed>
     */
    public function summarize(?int $placesId = null, ?int $citiesId = null): array
    {
        $estatesQuery = Estate::query()->where('status', 'active');

        if ($placesId) {
            $estatesQuery->where('places_id', $placesId);
        }

        if ($citiesId) {
            $estatesQuery->whereHas('place', fn ($q) => $q->where('cities_id', $citiesId));
        }

        $estateStats = (clone $estatesQuery)->selectRaw('
            COUNT(*) as listings_count,
            AVG(price) as avg_listed_price,
            MIN(price) as min_listed_price,
            MAX(price) as max_listed_price,
            AVG(roi) as avg_roi,
            AVG(space_of_estate) as avg_space
        ')->first();

        $predictionsQuery = PricePrediction::query();

        if ($placesId || $citiesId) {
            $predictionsQuery->whereHas('estate', function ($q) use ($placesId, $citiesId) {
                $q->where('status', 'active');
                if ($placesId) {
                    $q->where('places_id', $placesId);
                }
                if ($citiesId) {
                    $q->whereHas('place', fn ($p) => $p->where('cities_id', $citiesId));
                }
            });
        }

        $predictionStats = $predictionsQuery->selectRaw('
            COUNT(*) as predictions_count,
            AVG(predicted_price) as avg_predicted_price,
            AVG(price_difference_percent) as avg_difference_percent
        ')->first();

        $byPlace = Estate::query()
            ->where('status', 'active')
            ->when($placesId, fn ($q) => $q->where('places_id', $placesId))
            ->when($citiesId, fn ($q) => $q->whereHas('place', fn ($p) => $p->where('cities_id', $citiesId)))
            ->join('places', 'estates.places_id', '=', 'places.id')
            ->groupBy('places.id', 'places.name')
            ->orderByDesc(DB::raw('COUNT(estates.id)'))
            ->limit(10)
            ->get([
                'places.id as place_id',
                'places.name as place_name',
                DB::raw('COUNT(estates.id) as listings_count'),
                DB::raw('AVG(estates.price) as avg_listed_price'),
            ]);

        return [
            'filters' => [
                'places_id' => $placesId,
                'cities_id' => $citiesId,
            ],
            'listings' => [
                'count' => (int) ($estateStats->listings_count ?? 0),
                'avg_listed_price' => $estateStats->avg_listed_price !== null
                    ? round((float) $estateStats->avg_listed_price, 2) : null,
                'min_listed_price' => $estateStats->min_listed_price !== null
                    ? round((float) $estateStats->min_listed_price, 2) : null,
                'max_listed_price' => $estateStats->max_listed_price !== null
                    ? round((float) $estateStats->max_listed_price, 2) : null,
                'avg_roi' => $estateStats->avg_roi !== null
                    ? round((float) $estateStats->avg_roi, 4) : null,
                'avg_space' => $estateStats->avg_space !== null
                    ? round((float) $estateStats->avg_space, 2) : null,
            ],
            'predictions' => [
                'count' => (int) ($predictionStats->predictions_count ?? 0),
                'avg_predicted_price' => $predictionStats->avg_predicted_price !== null
                    ? round((float) $predictionStats->avg_predicted_price, 2) : null,
                'avg_difference_percent' => $predictionStats->avg_difference_percent !== null
                    ? round((float) $predictionStats->avg_difference_percent, 2) : null,
            ],
            'top_places_by_listings' => $byPlace->map(fn ($row) => [
                'place_id' => $row->place_id,
                'place_name' => $row->place_name,
                'listings_count' => (int) $row->listings_count,
                'avg_listed_price' => $row->avg_listed_price !== null
                    ? round((float) $row->avg_listed_price, 2) : null,
            ])->values()->all(),
        ];
    }
}
