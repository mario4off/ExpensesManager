<?php

namespace App\Providers;

use App\Services\Chat\ChatModel;
use App\Services\Chat\OllamaChatModel;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Ollama is the provisional chat provider; swap this binding to change it.
        $this->app->bind(ChatModel::class, OllamaChatModel::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Intentional: no "Crear y crear otro" button on any create page.
        CreateRecord::disableCreateAnother();

        // Intentional: every date field uses Filament's picker, not the browser's.
        DatePicker::configureUsing(fn (DatePicker $picker) => $picker->native(false));
    }
}
