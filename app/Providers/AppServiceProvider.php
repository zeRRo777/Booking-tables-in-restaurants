<?php

namespace App\Providers;

use App\Repositories\Contracts\PasswordResetRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\EloquentPasswordResetRepository;
use App\Repositories\Eloquent\EloquentUserRepository;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(PasswordResetRepositoryInterface::class, EloquentPasswordResetRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('l5-swagger.generate_always')) {
            Artisan::call('l5-swagger:generate');
        }

        RateLimiter::for('api', function (Request $request) {
            $limit = $request->user()
                ? Limit::perMinute(100)->by($request->user()->id)
                : Limit::perMinute(10)->by($request->ip());

            return $limit->response(function (Request $request, array $headers) {
                return response()->json([
                    'type'   => 'https://example.com/errors/rate-limiter',
                    'title'  => 'Rate Limiter',
                    'status' => 429,
                    'detail' => 'Слишком много запросов в минуту!',
                    'instance' => $request->getUri(),
                ], 429);
            });
        });
    }
}
