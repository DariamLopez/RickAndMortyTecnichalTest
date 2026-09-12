<?php

namespace App\Providers;

use App\Services\RickAndMorty\RickAndMortyClient;
use App\Services\RickAndMorty\RickAndMortyClientInterface;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use TokenGuard;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            RickAndMortyClientInterface::class, function () {
                return new RickAndMortyClient(
                    baseUri: config('services.rick_and_morty.base_uri'),
                    timeout: config('services.rick_and_morty.timeout', 5),
                    retryTimes: config('services.rick_and_morty.retry_times', 3),
                    retryDelayMs: config('services.rick_and_morty.retry_delay_ms', 200),
                );
            }
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        Auth::extend('token', fn ($app) => new TokenGuard($app['request']));
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
