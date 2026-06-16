<?php

namespace App\Filament\Portal\Pages;

use App\Filament\Portal\Concerns\HasPermissionBasedData;
use App\Models\Order;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class MyOrders extends Page
{
    use HasPermissionBasedData;

    protected string $view = 'filament.portal.pages.my-orders';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;
    protected static ?int $navigationSort = 2;

    protected string $viewOwnPermission = 'orders.view.own';
    protected string $viewAnyPermission = 'orders.view.any';

    // ✅ Navigation label — role se dynamic
    public static function getNavigationLabel(): string
    {
        return auth()->user()?->can('orders.view.any')
            ? 'All Orders'
            : 'My Orders';
    }

    // ✅ Page title — role se dynamic
    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return auth()->user()?->can('orders.view.any')
            ? 'All Orders'
            : 'My Orders';
    }

    public function getViewData(): array
    {
        $orders = $this->scopeQuery(
            Order::with('orderable')->latest()
        )->get();

        return compact('orders');
    }
}