<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Models\Payment;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // ── Payment Overview ──────────────────────────
                Section::make('Payment Details')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('payment_key')
                                    ->label('Payment ID')
                                    ->badge()
                                    ->color('gray')
                                    ->copyable(),

                                TextEntry::make('user.name')
                                    ->label('Customer'),

                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'completed'      => 'success',
                                        'pending'        => 'warning',
                                        'processing'     => 'info',
                                        'failed'         => 'danger',
                                        'cancelled'      => 'danger',
                                        'refunded'       => 'danger',
                                        'partial_refund' => 'warning',
                                        default          => 'gray',
                                    }),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextEntry::make('gateway')
                                    ->label('Gateway')
                                    ->badge()
                                    ->color(fn (string $state): string => match (strtolower($state)) {
                                        'stripe'   => 'info',
                                        'razorpay' => 'warning',
                                        'cashfree' => 'success',
                                        default    => 'gray',
                                    })
                                    ->formatStateUsing(fn (string $state) => strtoupper($state)),

                                TextEntry::make('payment_method')
                                    ->label('Payment Method')
                                    ->badge()
                                    ->color('gray')
                                    ->placeholder('-'),

                                TextEntry::make('paid_at')
                                    ->label('Paid At')
                                    ->dateTime('d M Y, h:i A')
                                    ->placeholder('-'),
                            ]),
                    ]),

                // ── Amount ────────────────────────────────────
                Section::make('Amount')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('amount')
                                    ->label('Amount Paid')
                                    ->money(fn ($record) => $record->currency ?? 'USD')
                                    ->weight('bold')
                                    ->color('success')
                                    ->size('lg'),

                                TextEntry::make('amount_refunded')
                                    ->label('Amount Refunded')
                                    ->money(fn ($record) => $record->currency ?? 'USD')
                                    ->color('danger')
                                    ->placeholder('No refund'),

                                TextEntry::make('currency')
                                    ->label('Currency')
                                    ->badge()
                                    ->color('gray'),
                            ]),
                    ]),

                // ── Gateway Info ──────────────────────────────
                Section::make('Gateway Information')
                    ->collapsed()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('gateway_payment_id')
                                    ->label('Gateway Payment ID')
                                    ->copyable()
                                    ->placeholder('-'),

                                TextEntry::make('gateway_order_id')
                                    ->label('Gateway Order ID')
                                    ->copyable()
                                    ->placeholder('-'),
                            ]),

                        TextEntry::make('gateway_response')
                            ->label('Raw Gateway Response')
                            ->formatStateUsing(fn ($state) => json_encode($state, JSON_PRETTY_PRINT))
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),

                // ── Notes ─────────────────────────────────────
                Section::make('Notes')
                    ->collapsed()
                    ->schema([
                        TextEntry::make('notes')
                            ->label('Notes')
                            ->placeholder('No notes')
                            ->columnSpanFull(),
                    ]),

                // ── Timeline ──────────────────────────────────
                Section::make('Timeline')
                    ->collapsed()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Created At')
                                    ->dateTime('d M Y, h:i A'),

                                TextEntry::make('refunded_at')
                                    ->label('Refunded At')
                                    ->dateTime('d M Y, h:i A')
                                    ->placeholder('-'),

                                TextEntry::make('failed_at')
                                    ->label('Failed At')
                                    ->dateTime('d M Y, h:i A')
                                    ->placeholder('-'),
                            ]),
                    ]),

                // ── Deleted ───────────────────────────────────
                Section::make('Deleted')
                    ->visible(fn (Payment $record): bool => $record->trashed())
                    ->schema([
                        TextEntry::make('deleted_at')
                            ->label('Deleted At')
                            ->dateTime('d M Y, h:i A')
                            ->color('danger'),
                    ]),
            ]);
    }
}