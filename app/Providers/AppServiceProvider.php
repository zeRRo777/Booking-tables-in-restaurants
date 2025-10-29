<?php

namespace App\Providers;

use App\Models\ReminderType;
use App\Models\ReservationStatuse;
use App\Models\Restaurant;
use App\Models\RestaurantChain;
use App\Models\RestaurantSchedule;
use App\Models\Review;
use App\Models\Role;
use App\Models\Table;
use App\Models\User;
use App\Policies\ChainPolicy;
use App\Policies\ReminderTypePolicy;
use App\Policies\ReservationStatusePolicy;
use App\Policies\RestaurantPolicy;
use App\Policies\RestaurantSchedulePolicy;
use App\Policies\ReviewPolicy;
use App\Policies\RolePolicy;
use App\Policies\TablePolicy;
use App\Policies\UserPolicy;
use App\Repositories\Contracts\ChainRepositoryInterface;
use App\Repositories\Contracts\EmailChangeRepositoryInterface;
use App\Repositories\Contracts\EmailVefiedRepositoryInterface;
use App\Repositories\Contracts\PasswordResetRepositoryInterface;
use App\Repositories\Contracts\PhoneChangeRepositoryInterface;
use App\Repositories\Contracts\PhoneVefiedRepositoryInterface;
use App\Repositories\Contracts\ReminderTypeInterface;
use App\Repositories\Contracts\ReservationStatuseRepositoryInterface;
use App\Repositories\Contracts\RestaurantRepositoryInterface;
use App\Repositories\Contracts\RestaurantScheduleRepositoryInterface;
use App\Repositories\Contracts\ReviewRepositoryInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\TableRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\UserTokensRepositoryInterface;
use App\Repositories\Eloquent\EloquentChainRepository;
use App\Repositories\Eloquent\EloquentEmailChangeRepository;
use App\Repositories\Eloquent\EloquentEmailVerifiedRepository;
use App\Repositories\Eloquent\EloquentPasswordResetRepository;
use App\Repositories\Eloquent\EloquentPhoneChangeRepository;
use App\Repositories\Eloquent\EloquentPhoneVerifiedRepository;
use App\Repositories\Eloquent\EloquentReminderTypeRepository;
use App\Repositories\Eloquent\EloquentReservationStatuseRepository;
use App\Repositories\Eloquent\EloquentRestaurantRepository;
use App\Repositories\Eloquent\EloquentRestaurantScheduleRepository;
use App\Repositories\Eloquent\EloquentReviewRepository;
use App\Repositories\Eloquent\EloquentRoleRepository;
use App\Repositories\Eloquent\EloquentTableRepository;
use App\Repositories\Eloquent\EloquentUserRepository;
use App\Repositories\Eloquent\EloquentUserTokensRepository;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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
        $this->app->bind(EmailChangeRepositoryInterface::class, EloquentEmailChangeRepository::class);
        $this->app->bind(UserTokensRepositoryInterface::class, EloquentUserTokensRepository::class);
        $this->app->bind(PhoneChangeRepositoryInterface::class, EloquentPhoneChangeRepository::class);
        $this->app->bind(EmailVefiedRepositoryInterface::class, EloquentEmailVerifiedRepository::class);
        $this->app->bind(PhoneVefiedRepositoryInterface::class, EloquentPhoneVerifiedRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, EloquentRoleRepository::class);
        $this->app->bind(ChainRepositoryInterface::class, EloquentChainRepository::class);
        $this->app->bind(RestaurantRepositoryInterface::class, EloquentRestaurantRepository::class);
        $this->app->bind(TableRepositoryInterface::class, EloquentTableRepository::class);
        $this->app->bind(ReminderTypeInterface::class, EloquentReminderTypeRepository::class);
        $this->app->bind(ReservationStatuseRepositoryInterface::class, EloquentReservationStatuseRepository::class);
        $this->app->bind(RestaurantScheduleRepositoryInterface::class, EloquentRestaurantScheduleRepository::class);
        $this->app->bind(ReviewRepositoryInterface::class, EloquentReviewRepository::class);
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

        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(RestaurantChain::class, ChainPolicy::class);
        Gate::policy(Restaurant::class, RestaurantPolicy::class);
        Gate::policy(Table::class, TablePolicy::class);
        Gate::policy(ReminderType::class, ReminderTypePolicy::class);
        Gate::policy(ReservationStatuse::class, ReservationStatusePolicy::class);
        Gate::policy(RestaurantSchedule::class, RestaurantSchedulePolicy::class);
        Gate::policy(Review::class, ReviewPolicy::class);
    }
}
