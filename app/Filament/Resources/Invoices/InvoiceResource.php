<?php

namespace App\Filament\Resources\Invoices;

use App\Filament\Resources\Invoices\Pages\ManageInvoices;
use App\Models\Invoice;
use App\Models\Payment;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;
use Nexbolt\Core\Services\InvoiceService;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $recordTitleAttribute = 'invoice_number';

    // ✅ Invoice manually create/edit nahi honi chahiye — sirf InvoiceService se
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('invoice_number')
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('Invoice #')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable(),

                TextColumn::make('item_description')
                    ->label('Description')
                    ->limit(35)
                    ->tooltip(fn ($record) => $record->item_description),

                TextColumn::make('total')
                    ->money(fn ($record) => $record->currency)
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'draft' => 'gray',
                        'issued' => 'success',
                        'void' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('generation_source')
                    ->label('Source')
                    ->badge()
                    ->color(fn (string $state) => $state === 'auto' ? 'info' : 'warning')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('issued_at')
                    ->label('Issued On')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'issued' => 'Issued',
                        'void' => 'Void',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                // ── View PDF ──────────────────────────────
                Action::make('viewPdf')
                    ->label('View PDF')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->visible(fn ($record) => !empty($record->pdf_path))
                    ->url(fn ($record) => Storage::disk('public')->url($record->pdf_path))
                    ->openUrlInNewTab(),

                // ── Regenerate PDF (agar missing ho ya admin chahe) ──
                Action::make('regeneratePdf')
                    ->label('Regenerate PDF')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn ($record) => $record->status !== 'void')
                    ->requiresConfirmation()
                    ->action(function (Invoice $record) {
                        app(InvoiceService::class)->generatePdf($record);

                        Notification::make()
                            ->title('Invoice PDF regenerated')
                            ->success()
                            ->send();
                    }),

                // ── Void ──────────────────────────────────
                Action::make('void')
                    ->label('Void')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === 'issued')
                    ->requiresConfirmation()
                    ->modalHeading('Void this invoice?')
                    ->modalDescription('This invoice will be marked as void and cannot be undone. The record will be kept for audit purposes.')
                    ->action(function (Invoice $record) {
                        app(InvoiceService::class)->void($record);

                        Notification::make()
                            ->title('Invoice voided')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->can('refunds.full-access')),
                ]),
            ])
            ->headerActions([
                // ── Manual generate — Payment select karke invoice banao ──
                Action::make('generateInvoice')
                    ->label('Generate Invoice')
                    ->icon('heroicon-o-plus')
                    ->schema([
                        \Filament\Forms\Components\Select::make('payment_id')
                            ->label('Select Payment')
                            ->options(
                                Payment::where('status', 'completed')
                                    ->whereDoesntHave('invoice')
                                    ->get()
                                    ->mapWithKeys(fn ($p) => [$p->id => "{$p->payment_key} — {$p->currency} {$p->amount}"])
                            )
                            ->required()
                            ->searchable(),
                    ])
                    ->action(function (array $data) {
                        $payment = Payment::find($data['payment_id']);

                        app(InvoiceService::class)->generate(
                            payment: $payment,
                            generatedBy: auth()->user(),
                            source: 'manual'
                        );

                        Notification::make()
                            ->title('Invoice generated successfully')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageInvoices::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}