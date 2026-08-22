<?php

namespace Modules\Ecommerce\Services;

use Illuminate\Support\Facades\Http;
use Modules\Ecommerce\Models\CodLocation;

/**
 * Hitung titik COD terdekat dari lokasi customer + ongkir berdasarkan jarak.
 * Tarif per-km & radius memakai konfigurasi shipping yang sudah ada (so.*).
 */
class CodShippingService
{
    /**
     * Quote COD: titik terdekat, jarak, dan ongkir.
     *
     * @return array{status: bool, message?: string, location_name?: string, address?: ?string, distance_km?: float, shipping_fee?: float}
     */
    public function quote(float $lat, float $lng): array
    {
        $points = CodLocation::active();

        if ($points->isEmpty()) {
            return ['status' => false, 'message' => 'Belum ada lokasi COD yang tersedia.'];
        }

        // Titik terdekat (garis lurus / haversine) — tabel kecil, hitung di PHP
        /** @var CodLocation|null $nearest */
        $nearest = null;
        $nearestKm = PHP_FLOAT_MAX;

        foreach ($points as $point) {
            $km = $this->haversine($lat, $lng, (float) $point->lat, (float) $point->lng);

            if ($km < $nearestKm) {
                $nearest = $point;
                $nearestKm = $km;
            }
        }

        if (! $nearest) {
            return ['status' => false, 'message' => 'Lokasi COD tidak ditemukan.'];
        }

        // Jarak tempuh (jalan) dari rumah customer ke titik COD terdekat
        $km = $this->roadDistance($lat, $lng, (float) $nearest->lat, (float) $nearest->lng)
            ?? round($nearestKm, 2);

        $maxRadius = (float) config('so.shipping.max_radius_km');
        if ($maxRadius > 0 && $km > $maxRadius) {
            return [
                'status' => false,
                'message' => "Lokasi Anda di luar radius layanan COD (maks {$maxRadius} km dari titik terdekat).",
            ];
        }

        return [
            'status' => true,
            'location_name' => $nearest->location_name,
            'address' => $nearest->address,
            'distance_km' => $km,
            // Fee flat per titik (jika diisi) menang; selain itu hitung dari jarak
            'shipping_fee' => $nearest->fee !== null
                ? (float) $nearest->fee
                : $this->shippingFee($km),
        ];
    }

    /**
     * Ongkir berdasarkan jarak (km): price_per_km * km, minimal min_fee.
     */
    public function shippingFee(float $distanceKm): float
    {
        $fee = ceil(max(0, $distanceKm)) * (float) config('so.shipping.price_per_km', 2500);

        return round(max($fee, (float) config('so.shipping.min_fee', 10000)), 2);
    }

    /**
     * Jarak berkendara via OSRM; null jika gagal (fallback haversine).
     */
    private function roadDistance(float $fromLat, float $fromLng, float $toLat, float $toLng): ?float
    {
        try {
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

            $km = ((float) $data['routes'][0]['distance']) / 1000;

            return $km > 0 ? round($km, 2) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function authHeaders(): array
    {
        $key = config('so.map.api_key');

        return $key ? ['Authorization' => $key] : [];
    }

    private function haversine(float $fromLat, float $fromLng, float $toLat, float $toLng): float
    {
        $earthRadius = 6371.0;

        $dLat = deg2rad($toLat - $fromLat);
        $dLng = deg2rad($toLng - $fromLng);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($fromLat)) * cos(deg2rad($toLat)) * sin($dLng / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
