<?php

namespace Modules\So\Services;

use Illuminate\Support\Facades\Http;

class DistanceService
{
    /**
     * Jarak berkendara (km) antara gudang dan titik tujuan.
     * Fallback ke haversine jika routing gagal.
     */
    public function distanceFromWarehouse(float $lat, float $lng): float
    {
        $warehouse = config('so.shipping.warehouse');

        return $this->distance(
            (float) $warehouse['lat'],
            (float) $warehouse['lng'],
            $lat,
            $lng
        );
    }

    public function distance(float $fromLat, float $fromLng, float $toLat, float $toLng): float
    {
        try {
            $road = $this->roadDistance($fromLat, $fromLng, $toLat, $toLng);
            if ($road !== null && $road > 0) {
                return round($road, 2);
            }
        } catch (\Throwable) {
            // fallback below
        }

        return round($this->haversine($fromLat, $fromLng, $toLat, $toLng), 2);
    }

    /**
     * Ongkir berdasarkan jarak (km): price_per_km * km, minimal min_fee.
     */
    public function shippingFee(float $distanceKm): float
    {
        $fee = $distanceKm * (float) config('so.shipping.price_per_km', 2500);

        return round(max($fee, (float) config('so.shipping.min_fee', 10000)), 2);
    }

    private function roadDistance(float $fromLat, float $fromLng, float $toLat, float $toLng): ?float
    {
        $baseUrl = rtrim((string) config('so.map.base_url'), '/');
        $url = "{$baseUrl}/route/v1/driving/{$fromLng},{$fromLat};{$toLng},{$toLat}?overview=false";

        $response = Http::timeout((int) config('so.map.timeout', 10))
            ->withHeaders($this->authHeaders())
            ->get($url);

        if ($response->failed()) {
            return null;
        }

        $data = $response->json();

        if (($data['code'] ?? null) !== 'Ok' || ! isset($data['routes'][0]['distance'])) {
            return null;
        }

        return ((float) $data['routes'][0]['distance']) / 1000;
    }

    private function authHeaders(): array
    {
        $key = config('so.map.api_key');

        return $key ? ['Authorization' => $key] : [];
    }

    /**
     * Haversine straight-line distance in km.
     */
    public function haversine(float $fromLat, float $fromLng, float $toLat, float $toLng): float
    {
        $earthRadius = 6371.0;

        $dLat = deg2rad($toLat - $fromLat);
        $dLng = deg2rad($toLng - $fromLng);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($fromLat)) * cos(deg2rad($toLat)) * sin($dLng / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
