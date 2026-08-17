<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WikimediaImageSearch
{
    public function firstUrl(string $query): ?string
    {
        $query = trim($query);
        if ($query === '') {
            return null;
        }

        try {
            $response = Http::timeout(12)
                ->withHeaders([
                    'User-Agent' => 'QuizFamilia/1.0 (https://quizemfamilia.com.br; contato)',
                ])
                ->acceptJson()
                ->get('https://commons.wikimedia.org/w/api.php', [
                    'action' => 'query',
                    'format' => 'json',
                    'generator' => 'search',
                    'gsrsearch' => $query,
                    'gsrnamespace' => 6,
                    'gsrlimit' => 1,
                    'prop' => 'imageinfo',
                    'iiprop' => 'url',
                    'iiurlwidth' => 800,
                ]);
        } catch (\Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $pages = $response->json('query.pages');
        if (! is_array($pages)) {
            return null;
        }

        foreach ($pages as $page) {
            $info = $page['imageinfo'][0] ?? null;
            $url = is_array($info) ? ($info['thumburl'] ?? $info['url'] ?? null) : null;
            if (is_string($url) && str_starts_with($url, 'https://')) {
                return $url;
            }
        }

        return null;
    }
}
