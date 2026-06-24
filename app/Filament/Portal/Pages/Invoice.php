<?php

namespace App\Filament\Portal\Pages;

use App\Filament\Portal\Concerns\HasPermissionBasedData;
use App\Models\Invoice as InvoiceModel;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class Invoice extends Page
{
    use HasPermissionBasedData;

    protected string $view = 'filament.portal.pages.invoice';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentChartBar;
    protected static ?int $navigationSort = 4;

    protected string $viewOwnPermission = 'invoices.view.own';
    protected string $viewAnyPermission = 'invoices.view.any';

    public static function getNavigationLabel(): string
    {
        $user = auth()->user();

        if ($user?->can('invoices.view.any')) {
            return 'All Invoices';
        }

        return 'My Invoices';
    }

    public function getTitle(): string
    {
        $user = auth()->user();

        if ($user?->can('invoices.view.any')) {
            return 'All Invoices';
        }

        return 'My Invoices';
    }

    public function getViewData(): array
    {
        $invoices = $this->scopeQuery(
            InvoiceModel::latest()
        )->get();

        return compact('invoices');
    }
}