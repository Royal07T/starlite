<?php

namespace App\Providers;

use App\Services\CartService;
use App\Services\DbNotificationService;
use App\View\Composers\AdminComposer;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Removed Telescope registration as it's not installed
        $this->app->singleton('cart', function ($app) {
            return app(CartService::class);
        });

        $this->app->singleton('db-notification', function ($app) {
            return app(DbNotificationService::class);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('google', \SocialiteProviders\Google\Provider::class);
        });
        View::composer('*', AdminComposer::class);

        Gate::before(function ($user, $ability) {
            if ($user && method_exists($user, 'hasRole') ? $user->hasRole('admin') : ($user->role ?? null) === 'admin') {
                return true;
            }
        });

        if (Schema::hasTable(config('optionbuilder.db_prefix') . 'settings')) {
            $this->app->setLocale(getLocaleToSet());
        }

        if (Schema::hasTable('jobs')) {
            dispatchQueueHeartbeat();
        }
    }
}
