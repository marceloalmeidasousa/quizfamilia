<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class GeoLookup
{
    /**
     * @return array{country: ?string, city: ?string}
     */
    public function lookup(?string $ip): array
    {
        $empty = ['country' => null, 'city' => null];

        if ($ip === null || $ip === '' || $this->isPrivateIp($ip)) {
            return $empty;
        }

        $cacheKey = 'geo:'.md5($ip);

        try {
            return Cache::remember($cacheKey, now()->addDays(7), function () use ($ip, $empty) {
                $response = Http::timeout(2)
                    ->acceptJson()
                    ->get("http://ip-api.com/json/{$ip}", [
                        'fields' => 'status,country,regionName,city',
                        'lang' => 'pt',
                    ]);

                if (! $response->ok() || ($response->json('status') !== 'success')) {
                    return $empty;
                }

                $city = $response->json('city') ?: $response->json('regionName');

                return [
                    'country' => $response->json('country'),
                    'city' => $city ?: null,
                ];
            });
        } catch (Throwable) {
            return $empty;
        }
    }

    private function isPrivateIp(string $ip): bool
    {
        if (! filter_var($ip, FILTER_VALIDATE_IP)) {
            return true;
        }

        return ! filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE,
        );
    }
}
