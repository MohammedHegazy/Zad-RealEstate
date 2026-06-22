<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PricePredictionClient
{
    /**
     * @param  array<string, mixed>  $payload  Flask /predict body
     * @return array{predicted_price: float}
     */
    public function predict(array $payload): array
    {
        $url = config('ml.price_prediction.url').'/predict';
        $timeout = config('ml.price_prediction.timeout_seconds');
        $connectTimeout = config('ml.price_prediction.connect_timeout_seconds');

        try {
            $response = Http::timeout($timeout)
                ->connectTimeout($connectTimeout)
                ->acceptJson()
                ->asJson()
                ->post($url, $payload);
        } catch (ConnectionException $e) {
            throw new RuntimeException(
                'Price prediction service is unavailable. Ensure the ML service is running.',
                previous: $e
            );
        }

        if ($response->failed()) {
            $error = $response->json('error') ?? $response->body();

            throw new RuntimeException(
                is_string($error) && $error !== ''
                    ? 'Price prediction failed: '.$error
                    : 'Price prediction service returned an error.',
            );
        }

        $predicted = $response->json('predicted_price');

        if ($predicted === null || ! is_numeric($predicted)) {
            throw new RuntimeException('Price prediction service returned an invalid response.');
        }

        return [
            'predicted_price' => round((float) $predicted, 2),
        ];
    }
}
