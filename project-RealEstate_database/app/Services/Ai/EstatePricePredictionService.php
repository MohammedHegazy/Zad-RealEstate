<?php

namespace App\Services\Ai;

use App\Models\Estate;
use App\Models\Places;
use App\Models\PricePrediction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class EstatePricePredictionService
{
    public function __construct(
        private readonly PricePredictionClient $client,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function predictForEstate(Estate $estate, ?User $user = null): array
    {
        $estate->loadMissing(['place.city']);

        $payload = $this->buildPayloadFromEstate($estate);
        $result = $this->client->predict($payload);

        return $this->formatResult(
            predictedPrice: $result['predicted_price'],
            payload: $payload,
            listedPrice: $estate->price !== null ? (float) $estate->price : null,
            estateId: $estate->id,
            userId: $user?->id,
            placeLabel: (string) $payload['place'],
        );
    }

    /**
     * Ad-hoc prediction (e.g. before listing is saved).
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    public function predictFromInput(array $validated, ?User $user = null): array
    {
        $payload = $this->buildPayloadFromArray($validated);
        $result = $this->client->predict($payload);

        $listedPrice = isset($validated['price']) ? (float) $validated['price'] : null;

        return $this->formatResult(
            predictedPrice: $result['predicted_price'],
            payload: $payload,
            listedPrice: $listedPrice,
            estateId: isset($validated['estate_id']) ? (int) $validated['estate_id'] : null,
            userId: $user?->id,
            placeLabel: (string) $payload['place'],
        );
    }

    /**
     * Build Flask-compatible payload from an Estate model.
     *
     * @return array<string, mixed>
     */
    public function buildPayloadFromEstate(Estate $estate): array
    {
        $place = $estate->place;
        $location = $this->resolveLocationLabel($place);

        return $this->buildPayloadFromArray([
            'place' => $location,
            'space_of_estate' => $estate->space_of_estate,
            'is_furnished' => $estate->is_furnished,
            'floor' => $estate->floor,
            'num_of_bedrooms' => $estate->num_of_bedrooms,
            'num_of_livingrooms' => $estate->num_of_livingrooms,
            'num_of_receptions' => $estate->num_of_receptions,
            'num_of_bathrooms' => $estate->num_of_bathrooms,
            'num_of_kitchens' => $estate->num_of_kitchens,
            'num_of_balconies' => $estate->num_of_balconies,
            'date_of_build' => $estate->date_of_build,
        ]);
    }

    /**
     * Normalize request/array input to the exact keys expected by server.py.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function buildPayloadFromArray(array $data): array
    {
        if (! empty($data['places_id']) && empty($data['place'])) {
            $placeModel = Places::with('city')->find($data['places_id']);
            $data['place'] = $placeModel ? $this->resolveLocationLabel($placeModel) : null;
        }
        // معالجة تاريخ البناء
        $dateOfBuild = $data['date_of_build'] ?? null;
        if ($dateOfBuild instanceof \DateTimeInterface) {
            $dateOfBuild = $dateOfBuild->format('Y-m-d');
        } elseif (is_string($dateOfBuild) && $dateOfBuild !== '') {
            try {
                $dateOfBuild = Carbon::parse($dateOfBuild)->format('Y-m-d');
            } catch (\Throwable) {
                $dateOfBuild = '2000-01-01';
            }
        } else {
            $dateOfBuild = '2000-01-01';
        }
        //يحول المفتاح is_furnished إلى 0 أو 1
        //لان ml يقبل فقط 0 أو 1
        $isFurnished = $data['is_furnished'] ?? false;
        if (is_string($isFurnished)) {
            $isFurnished = filter_var($isFurnished, FILTER_VALIDATE_BOOLEAN);
        }

        return [
            'place' => (string) ($data['place'] ?? ''),
            'space_of_estate' => (float) ($data['space_of_estate'] ?? 0),
            'is_furnished' => $isFurnished ? 1 : 0,
            'floor' => (float) ($data['floor'] ?? 0),
            'num_of_bedrooms' => (float) ($data['num_of_bedrooms'] ?? 0),
            'num_of_livingrooms' => (float) ($data['num_of_livingrooms'] ?? 0),
            // Flask model was trained with this key spelling (melhemm API typo).
            'num_of_receptioins' => (float) ($data['num_of_receptioins'] ?? $data['num_of_receptions'] ?? 0),
            'num_of_bathrooms' => (float) ($data['num_of_bathrooms'] ?? 0),
            'num_of_kitchens' => (float) ($data['num_of_kitchens'] ?? 0),
            'num_of_balconies' => (float) ($data['num_of_balconies'] ?? 0),
            'date_of_build' => $dateOfBuild,
        ];
    }

    private function resolveLocationLabel(?Places $place): string
    {
        if ($place === null) {
            return '';
        }

        $field = config('ml.price_prediction.location_field', 'city');

        if ($field === 'place') {
            return (string) $place->name;
        }

        return (string) ($place->city?->name ?? $place->name);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function formatResult(
        float $predictedPrice,
        array $payload,
        ?float $listedPrice,
        ?int $estateId,
        ?int $userId,
        string $placeLabel,
    ): array {
        $difference = null;
        $differencePercent = null;
        $insight = null;

        if ($listedPrice !== null && $listedPrice > 0) {
            $difference = round($predictedPrice - $listedPrice, 2);
            $differencePercent = round(($difference / $listedPrice) * 100, 2);

            if (abs($differencePercent) < 5) {
                $insight = 'aligned_with_model';
            } elseif ($difference > 0) {
                $insight = 'listed_below_prediction';
            } else {
                $insight = 'listed_above_prediction';
            }
        }

        $data = [
            'predicted_price' => $predictedPrice,
            'listed_price' => $listedPrice,
            'price_difference' => $difference,
            'price_difference_percent' => $differencePercent,
            'valuation_insight' => $insight,
            'input_features' => $payload,
        ];

        if (config('ml.price_prediction.log_predictions')) {
            $record = PricePrediction::create([
                'user_id' => $userId,
                'estate_id' => $estateId,
                'place_label' => $placeLabel,
                'input_features' => $payload,
                'predicted_price' => $predictedPrice,
                'listed_price' => $listedPrice,
                'price_difference' => $difference,
                'price_difference_percent' => $differencePercent,
                'valuation_insight' => $insight,
            ]);

            $data['prediction_id'] = $record->id;
        }

        return $data;
    }
}
