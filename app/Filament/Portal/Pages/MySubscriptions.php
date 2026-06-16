<?php

namespace App\Filament\Portal\Pages;

use App\Filament\Portal\Concerns\HasPermissionBasedData;
use App\Models\Subscription;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class MySubscriptions extends Page
{
    use HasPermissionBasedData;

    protected string $view = 'filament.portal.pages.my-subscriptions';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;
    protected static ?int $navigationSort = 1;

    protected string $viewOwnPermission = 'subscriptions.view.own';
    protected string $viewAnyPermission = 'subscriptions.view.any';

    // ✅ Navigation label — role se dynamic
    public static function getNavigationLabel(): string
    {
        return auth()->user()?->can('subscriptions.view.any')
            ? 'All Subscriptions'
            : 'My Subscriptions';
    }

    // ✅ Page title — role se dynamic
    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return auth()->user()?->can('subscriptions.view.any')
            ? 'All Subscriptions'
            : 'My Subscriptions';
    }

    public function getViewData(): array
    {
        $subscriptions = $this->scopeQuery(
            Subscription::with('plan')->latest()
        )->get();

        // Active — hamesha sirf apni
        $active = Subscription::where('user_id', auth()->id())
            ->where('status', 'active')
            ->with('plan')
            ->first();

        return compact('subscriptions', 'active');
    }
}