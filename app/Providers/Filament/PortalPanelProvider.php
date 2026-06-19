<?php

namespace App\Providers\Filament;

use App\Models\Setting;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class PortalPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $favicon = Cache::remember('site_favicon', 3600, function () {
            $setting = Setting::where('key', 'site_favicon')->first();
            return $setting ? $setting->value : null;
        });
        return $panel
            ->id('portal')
            ->path('portal')
            ->viteTheme('resources/css/filament/portal/theme.css')
            ->login()
            ->registration(\App\Filament\Portal\Pages\Auth\Register::class)
            ->passwordReset()
            ->homeUrl('/')
            ->brandName('NexFlow')
            ->colors([
                'primary' => Color::Indigo,
            ])
            ->spa()
            ->favicon($favicon ? Storage::url($favicon) : asset('images/favicon.png'))
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn() => \Illuminate\Support\Facades\Blade::render('@vite("resources/js/app.js")'),
            )
            ->discoverResources(in: app_path('Filament/Portal/Resources'), for: 'App\Filament\Portal\Resources')
            ->discoverPages(in: app_path('Filament/Portal/Pages'), for: 'App\Filament\Portal\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->navigationGroups([
                    NavigationGroup::make('Management')
                        ->collapsible(false),
                ])

            // ── Notification Bell ──────────────────────────────
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_AFTER,
                fn () => auth()->check()
                    ? \Illuminate\Support\Facades\Blade::render('<livewire:notification-bell />')
                    : '',
            )
            ->discoverWidgets(in: app_path('Filament/Portal/Widgets'), for: 'App\Filament\Portal\Widgets')
            ->widgets([
                AccountWidget::class,
                
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                
            ]);
    }
}
