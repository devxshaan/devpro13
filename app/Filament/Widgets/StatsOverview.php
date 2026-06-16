<?php

namespace App\Filament\Widgets;

use Akaunting\Money\Currency;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\User;
use App\Services\CurrencyConverter;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{

    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';
    
    protected function getStats(): array
    {
        $displayCurrency = Setting::get('default_currency', 'USD');
        $converter       = app(CurrencyConverter::class);

        // ── Total Revenue ─────────────────────────────────────
        $totalRevenue = 0;
        Payment::where('status', 'completed')
            ->select('amount', 'currency')
            ->get()
            ->each(function ($payment) use (&$totalRevenue, $displayCurrency, $converter) {
                $from          = $payment->currency ?? $displayCurrency;
                $totalRevenue += $converter->convert($payment->amount, $from, $displayCurrency);
            });

        $formattedRevenue = $converter->format($totalRevenue, $displayCurrency);

        // ── Currency Icon ─────────────────────────────────────
        $currencyIcon = match($displayCurrency) {
            'INR'        => 'heroicon-m-currency-rupee',
            'EUR'        => 'heroicon-m-currency-euro',
            'GBP'        => 'heroicon-m-currency-pound',
            'JPY', 'YEN' => 'heroicon-m-currency-yen',
            default      => 'heroicon-m-currency-dollar',
        };

        // ── Stats ─────────────────────────────────────────────
        $totalUsers = User::whereDoesntHave('roles', function ($query) {
            $query->where('name', config('roles.super_admin'));
        })->count();
        $newUsersToday = User::whereDoesntHave('roles', function ($query) {
            $query->where('name', config('roles.super_admin'));
        })->whereDate('created_at', today())->count();
        $activeSubscriptions = Subscription::where('status', 'active')->count();
        $trialSubscriptions  = Subscription::where('status', 'trial')->count();
        $pendingOrders       = Order::whereIn('status', ['draft', 'pending'])->count();
        $totalOrders         = Order::where('status', 'confirmed')->count();

        return [
            // ── Total Users ───────────────────────────────────
            Stat::make('Total Users', number_format($totalUsers))
                ->description($newUsersToday > 0
                    ? "+{$newUsersToday} new today"
                    : 'Registered users'
                )
                ->descriptionIcon('heroicon-m-users')
                ->color('info')
                ->chart([3, 5, 2, 8, 4, 11, $totalUsers > 0 ? $totalUsers : 1]),

            // ── Total Revenue ─────────────────────────────────
            Stat::make('Total Revenue', $formattedRevenue)
                ->description('From completed payments')
                ->descriptionIcon($currencyIcon)
                ->color('success')
                ->chart([7, 2, 10, 3, 15, 4, 17]),

            // ── Active Subscriptions ──────────────────────────
            Stat::make('Active Subscriptions', $activeSubscriptions)
                ->description($trialSubscriptions > 0
                    ? "{$trialSubscriptions} on trial"
                    : 'Currently active members'
                )
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),

            // ── Orders ────────────────────────────────────────
            Stat::make('Confirmed Orders', $totalOrders)
                ->description($pendingOrders > 0
                    ? "{$pendingOrders} pending payment"
                    : 'All orders confirmed'
                )
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color($pendingOrders > 0 ? 'warning' : 'success'),
        ];
    }
}