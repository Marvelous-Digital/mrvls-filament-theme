<?php

namespace Mrvls\FilamentTheme;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\ServiceProvider;

class MrvlsThemeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'mrvls-filament-theme');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/mrvls-filament-theme'),
        ], 'mrvls-filament-theme-views');

        $this->publishes([
            __DIR__ . '/../resources/css/theme.css' => resource_path('css/vendor/mrvls-filament-theme/theme.css'),
        ], 'mrvls-filament-theme-styles');

        FilamentAsset::register([
            Css::make('theme', __DIR__ . '/../resources/css/theme.css'),
        ], package: 'mrvls/filament-theme');
    }
}
