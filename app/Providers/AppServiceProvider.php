<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Profile;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\ResetAppPassword;
use App\Observers\OrderObserver;
use App\Observers\PaymentObserver;
use Filament\Auth\Notifications\ResetPassword as FilamentResetPassword;
use App\Observers\ProfileObserver;
use App\Observers\UserObserver;
use App\Policies\OrderPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\ProfilePolicy;
use App\Policies\SubscriptionPolicy;
use App\Services\Payment\PaymentService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PaymentService::class);
        $this->app->singleton(\App\Services\CurrencyConverter::class);

        $this->app->bind(
            FilamentResetPassword::class,
            ResetAppPassword::class,
        );
    }

    

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        
        User::observe(UserObserver::class);
        Profile::observe(ProfileObserver::class);
        Order::observe(OrderObserver::class);
        Payment::observe(PaymentObserver::class);

        Gate::before(function ($user, $ability) {
            if ($user->hasRole(config('roles.super_admin'))) {
                return true;
            }
        });

        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(Subscription::class, SubscriptionPolicy::class);
        Gate::policy(Profile::class, ProfilePolicy::class);
    }
}
