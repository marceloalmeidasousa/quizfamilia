<?php

namespace App\Providers;

use App\Support\Brand;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Fallback para CLI / erros antes do middleware de request.
        View::share('brand', Brand::resolve(null));
    }
}
