<?php

namespace Marvelous\FilamentModernSaas;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\ServiceProvider;

class ModernSaasThemeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'filament-modern-saas');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/filament-modern-saas'),
        ], 'filament-modern-saas-views');

        $this->publishes([
            __DIR__ . '/../resources/css/theme.css' => resource_path('css/vendor/filament-modern-saas/theme.css'),
        ], 'filament-modern-saas-styles');

        FilamentAsset::register([
            Css::make('theme', __DIR__ . '/../resources/css/theme.css'),
        ], package: 'marvelous/filament-modern-saas');
    }
}
