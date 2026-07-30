<?php

namespace App\Http\Middleware;

use App\Support\Brand;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ResolveBrand
{
    public function handle(Request $request, Closure $next): Response
    {
        $brand = Brand::resolve($request);

        app()->instance('brand', $brand);
        config(['app.name' => $brand['name']]);
        View::share('brand', $brand);

        return $next($request);
    }
}
