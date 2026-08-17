<?php

namespace App\Http\Middleware;

use App\Support\Brand;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureQuizEduHost
{
    public function handle(Request $request, Closure $next): Response
    {
        $brand = app()->bound('brand') ? app('brand') : Brand::resolve($request);

        if (($brand['key'] ?? null) !== 'quizedu') {
            abort(404);
        }

        return $next($request);
    }
}
