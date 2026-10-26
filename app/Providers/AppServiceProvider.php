<?php

namespace App\Providers;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\ServiceProvider;

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
        // Intentional: no "Crear y crear otro" button on any create page.
        CreateRecord::disableCreateAnother();
    }
}
