<?php

namespace App\Filament\Portal\Pages;

use App\Filament\Portal\Concerns\HasPermissionBasedData;
use App\Models\Payment;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class MyPayments extends Page
{
    use HasPermissionBasedData;

    protected string $view = 'filament.portal.pages.my-payments';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;
    protected static ?int $navigationSort = 3;

    protected string $viewOwnPermission = 'payments.view.own';
    protected string $viewAnyPermission = 'payments.view.any';

    // ✅ Navigation label — role se dynamic
    public static function getNavigationLabel(): string
    {
        $user = auth()->user();

        if ($user?->can('payments.view.any')) {
            return 'All Payments';
        }

        return 'My Payments';
    }

    // ✅ Page title — role se dynamic
    public function getTitle(): string
    {
        $user = auth()->user();

        if ($user?->can('payments.view.any')) {
            return 'All Payments';
        }

        return 'My Payments';
    }

    public function getViewData(): array
    {
        $payments = $this->scopeQuery(
            Payment::latest()
        )->get();

        return compact('payments');
    }
}