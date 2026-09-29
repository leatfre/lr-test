<?php

namespace App\Providers;

use App\Diagnostics\CheckRegistry;
use App\Diagnostics\DefaultCheckRegistry;
use Illuminate\Support\ServiceProvider;

class DiagnosticsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CheckRegistry::class, DefaultCheckRegistry::class);
    }
}
