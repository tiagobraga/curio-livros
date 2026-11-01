<?php

namespace App\Providers;

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
        if (config('app.url')) {
            \Illuminate\Support\Facades\URL::forceRootUrl(config('app.url'));
        }

        if (request()->header('X-Forwarded-Proto') === 'https' || request()->server('HTTP_X_FORWARDED_PROTO') === 'https' || app()->environment('production') || str_contains(config('app.url'), 'https')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Auto-executa migrações pendentes caso as novas colunas ainda não tenham sido migradas no banco
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('libraries') && !\Illuminate\Support\Facades\Schema::hasColumn('libraries', 'custom_fields')) {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Falha ao auto-executar migrações: ' . $e->getMessage());
        }
    }
}
