<?php

namespace App\Providers;

use Log1x\AcfComposer\AcfComposer;
use Roots\Acorn\Sage\SageServiceProvider;

class ThemeServiceProvider extends SageServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        parent::register();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        if (class_exists(AcfComposer::class)) {
            $composer = $this->app->make(AcfComposer::class);
            $themePath = get_stylesheet_directory() . '/app';
            add_filter('acf/init', function () use ($composer, $themePath) {
                $composer->registerPath($themePath);
            }, 1);
        }
    }
}
