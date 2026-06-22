<?php

namespace App\Filament\Portal\Pages;

use App\Filament\Portal\Concerns\HasPermissionBasedData;
use App\Models\Payment;
use App\Models\Setting;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Notifications\Notification;
use Nexbolt\Core\Services\Payment\RefundService;

class MyPayments extends Page
{
    use HasPermissionBasedData;

    protected string $view = 'filament.portal.pages.my-payments';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;
    protected static ?int $navigationSort = 3;

    protected string $viewOwnPermission = 'payments.view.own';
    protected string $viewAnyPermission = 'payments.view.any';

    public static function getNavigationLabel(): string
    {
        $user = auth()->user();

        if ($user?->can('payments.view.any')) {
            return 'All Payments';
        }

        return 'My Payments';
    }

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

   
    public function getRefundsEnabledProperty(): bool
    {
        return (bool) Setting::get('allow_user_refund_requests', false);
    }

    
    public function requestRefund(int $paymentId): void
    {
        $payment = Payment::findOrFail($paymentId);
        $user = auth()->user();

        
        if ($payment->user_id !== $user->id && !$user->can('payments.view.any')) {
            abort(403);
        }

        
        $existing = $payment->refunds()
            ->whereIn('status', ['requested', 'pending', 'approved', 'processing'])
            ->exists();

        if ($existing) {
            Notification::make()
                ->title('A refund request is already in progress for this payment.')
                ->warning()
                ->send();
            return;
        }

        $remaining = $payment->amount - $payment->amount_refunded;

        app(RefundService::class)->request(
            payment: $payment,
            amount: $remaining,
            reason: 'Requested by customer via portal',
            requestedBy: $user
        );

        Notification::make()
            ->title('Refund request submitted successfully.')
            ->success()
            ->send();
    }
}