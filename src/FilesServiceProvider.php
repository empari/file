<?php
namespace Empari\Laravel\Files;

use Illuminate\Support\ServiceProvider;

class FileServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Migrations
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations/'),
        ], 'migrations');

        // Vendors
        $this->app->register(\Cviebrock\EloquentSluggable\ServiceProvider::class);
        $this->app->register(\Intervention\Image\ImageServiceProvider::class);
        $this->app->register(\Spatie\Tags\TagsServiceProvider::class);
    }
}