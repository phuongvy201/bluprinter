<?php

namespace App\Providers;

use App\Services\CurrencyService;
use App\Models\Order;
use App\Models\ReturnRequest;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\CollectionAiMatchService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS in production to avoid Mixed Content issues
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('studio-ai-generate', function (Request $request) {
            $n = max(1, (int) (\App\Support\StudioAiSettings::resolved()['generate_per_minute'] ?? 6));

            return Limit::perMinute($n)->by($request->ip());
        });
        RateLimiter::for('studio-ai-improve', function (Request $request) {
            $n = max(1, (int) (\App\Support\StudioAiSettings::resolved()['improve_per_minute'] ?? 12));

            return Limit::perMinute($n)->by($request->ip());
        });
        RateLimiter::for('studio-ai-try-on', function (Request $request) {
            $n = max(1, (int) (\App\Support\StudioAiSettings::resolved()['try_on_per_minute'] ?? 6));

            return Limit::perMinute($n)->by($request->ip());
        });

        // Share currency information with all views
        View::composer('*', function ($view) {
            $currency = CurrencyService::getCurrentCurrency();
            $currencyRate = CurrencyService::getCurrentCurrencyRate();
            $currencySymbol = CurrencyService::getCurrencySymbol();

            // Sidebar badges for admin/ad-partner: new orders (pending) + return requests (pending)
            $sidebarPendingOrders = 0;
            $sidebarPendingReturns = 0;
            $returnsSeenAt = Session::get('returns_seen_at');

            $user = Auth::user();
            if ($user && ($user->hasRole('admin') || $user->hasRole('ad-partner'))) {
                $sidebarPendingOrders = Order::where('status', 'pending')->count();
                $sidebarPendingReturns = ReturnRequest::where('status', 'pending')
                    ->when($returnsSeenAt, function ($q) use ($returnsSeenAt) {
                        $q->where('updated_at', '>', $returnsSeenAt);
                    })
                    ->count();
            }
            
            $view->with([
                'currentCurrency' => $currency,
                'currentCurrencyRate' => $currencyRate,
                'currentCurrencySymbol' => $currencySymbol,
                'sidebarPendingOrders' => $sidebarPendingOrders,
                'sidebarPendingReturns' => $sidebarPendingReturns,
            ]);
        });
    }
}
