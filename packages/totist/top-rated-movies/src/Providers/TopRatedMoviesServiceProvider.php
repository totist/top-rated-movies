<?php

namespace Totist\TopRatedMovies\Providers;

use Illuminate\Support\ServiceProvider;

class TopRatedMoviesServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/tmdb.php' => config_path('tmdb.php'),
        ], 'tmdb_config');

        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');

        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'top-rated-movies');

        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }
}
