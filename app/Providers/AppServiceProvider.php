<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Helper untuk menangani storage URL (Supabase atau local)
        Blade::directive('storageUrl', function ($expression) {
            return "<?php 
                \$path = $expression;
                if (strpos(\$path, 'supabase.co') !== false || strpos(\$path, 'http') === 0) {
                    echo \$path;
                } else {
                    echo Storage::url(\$path);
                }
            ?>";
        });
    }
}
