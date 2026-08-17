<?php

namespace App\Http\Middleware;

use App\Models\SiteVisit;
use App\Services\GeoLookup;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogSiteVisit
{
    /** Minutos entre registros iguais (mesmo session + path). */
    private const DEDUPE_MINUTES = 30;

    public function __construct(private GeoLookup $geo) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->shouldLog($request)) {
            return $response;
        }

        try {
            $this->record($request);
        } catch (\Throwable) {
            // Analytics never break the site.
        }

        return $response;
    }

    private function shouldLog(Request $request): bool
    {
        if (! $request->isMethod('GET')) {
            return false;
        }

        if ($request->ajax() || $request->expectsJson()) {
            return false;
        }

        $path = '/'.ltrim($request->path(), '/');

        // Evita flood de polling do ao vivo e health checks.
        if (preg_match('#/estado$#', $path) || $path === '/up') {
            return false;
        }

        if ($request->is('login', 'logout', 'painel', 'painel/*')) {
            return false;
        }

        return true;
    }

    private function record(Request $request): void
    {
        $path = '/'.ltrim($request->path(), '/');
        if ($path === '//') {
            $path = '/';
        }

        $sessionId = $request->session()->getId();
        $since = now()->subMinutes(self::DEDUPE_MINUTES);

        $exists = SiteVisit::query()
            ->where('session_id', $sessionId)
            ->where('path', $path)
            ->where('visited_at', '>=', $since)
            ->exists();

        if ($exists) {
            return;
        }

        $ip = $request->ip();
        $geo = $this->geo->lookup($ip);

        SiteVisit::query()->create([
            'path' => mb_substr($path, 0, 255),
            'method' => $request->method(),
            'ip_address' => $ip,
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'country' => $geo['country'],
            'city' => $geo['city'],
            'session_id' => $sessionId,
            'visited_at' => now(),
        ]);
    }
}
