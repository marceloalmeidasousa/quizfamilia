<?php

namespace App\Support;

use Illuminate\Http\Request;

class Brand
{
    /**
     * @return array{
     *     key: string,
     *     name: string,
     *     name_html: string,
     *     tagline: string,
     *     description: string
     * }
     */
    public static function resolve(?Request $request = null): array
    {
        $sites = config('brand.sites', []);
        $forced = config('brand.force');

        if (is_string($forced) && $forced !== '' && isset($sites[$forced])) {
            return self::normalize($forced, $sites[$forced]);
        }

        $host = strtolower((string) ($request?->getHost() ?? ''));
        $host = preg_replace('/:\d+$/', '', $host) ?: $host;

        foreach ($sites as $key => $site) {
            $hosts = array_map('strtolower', $site['hosts'] ?? []);
            if (in_array($host, $hosts, true)) {
                return self::normalize($key, $site);
            }
        }

        $default = (string) config('brand.default', 'quizfamilia');
        if (! isset($sites[$default])) {
            $default = array_key_first($sites) ?: 'quizfamilia';
            $sites[$default] ??= [
                'name' => config('app.name', 'Quiz'),
                'name_html' => e(config('app.name', 'Quiz')),
                'tagline' => 'Feito para jogar junto.',
                'description' => config('app.name', 'Quiz'),
            ];
        }

        return self::normalize($default, $sites[$default]);
    }

    /**
     * @param  array<string, mixed>  $site
     * @return array{key: string, name: string, name_html: string, tagline: string, description: string}
     */
    private static function normalize(string $key, array $site): array
    {
        $name = (string) ($site['name'] ?? 'Quiz');

        return [
            'key' => $key,
            'name' => $name,
            'name_html' => (string) ($site['name_html'] ?? e($name)),
            'tagline' => (string) ($site['tagline'] ?? 'Feito para jogar junto.'),
            'description' => (string) ($site['description'] ?? $name),
        ];
    }
}
