<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Services\CurrencyConverter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                // ── Identity ────────────────────────────────
                TextColumn::make('payment_key')
                    ->label('Payment ID')
                    ->badge()
                    ->color('gray')
                    ->copyable()
                    ->searchable(),

                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                // ── Gateway ────────────────────────────────
                TextColumn::make('gateway')
                    ->label('Gateway')
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'stripe'   => 'info',
                        'razorpay' => 'warning',
                        'cashfree' => 'success',
                        'manual', 'cash' => 'gray',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => strtoupper($state)),

                // ── AMOUNT (FIXED PROPERLY) ────────────────
                TextColumn::make('amount')
                    ->label('Amount')
                    ->sortable()
                    ->formatStateUsing(function ($state, $record) {
                        return $record->formatted('amount');
                    }),

                // ── REFUNDED AMOUNT (FIXED) ────────────────
                TextColumn::make('amount_refunded')
                    ->label('Refunded')
                    ->sortable()
                    ->formatStateUsing(function ($state, $record) {
                        return $record->formatted('amount_refunded');
                    })
                    ->color('danger')
                    ->toggleable(isToggledHiddenByDefault: true),

                // ── Status ──────────────────────────────────
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed'      => 'success',
                        'pending'        => 'warning',
                        'processing'     => 'info',
                        'failed', 'cancelled', 'refunded' => 'danger',
                        'partial_refund' => 'warning',
                        default          => 'gray',
                    }),

                // ── Method ──────────────────────────────────
                TextColumn::make('payment_method')
                    ->label('Method')
                    ->badge()
                    ->color('gray')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                // ── Dates ───────────────────────────────────
                TextColumn::make('paid_at')
                    ->label('Paid At')
                    ->dateTime('d M Y, h:i A')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending'        => 'Pending',
                        'processing'     => 'Processing',
                        'completed'      => 'Completed',
                        'failed'         => 'Failed',
                        'cancelled'      => 'Cancelled',
                        'refunded'       => 'Refunded',
                        'partial_refund' => 'Partial Refund',
                    ]),

                SelectFilter::make('gateway')
                    ->options([
                        'stripe'   => 'Stripe',
                        'razorpay' => 'Razorpay',
                        'cashfree' => 'Cashfree',
                        'manual'   => 'Manual',
                        'cash'     => 'Cash',
                    ]),

                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}