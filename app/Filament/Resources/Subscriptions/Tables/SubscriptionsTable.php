<?php

namespace App\Filament\Resources\Subscriptions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class SubscriptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                // ── Identity ──────────────────────────────────
                TextColumn::make('subscription_key')
                    ->label('Subscription ID')
                    ->badge()
                    ->color('gray')
                    ->copyable()
                    ->searchable(),

                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                // ── Plan ──────────────────────────────────────
                TextColumn::make('plan.name')
                    ->label('Plan')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                // ── Price ─────────────────────────────────────
                TextColumn::make('price_at_subscription')
                    ->label('Price')
                    ->sortable(query: fn ($query, $direction) => $query->orderBy('amount', $direction)),

                // ── Status ────────────────────────────────────
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active'   => 'success',
                        'trial'    => 'info',
                        'paused'   => 'warning',
                        'past_due' => 'warning',
                        'cancelled'=> 'danger',
                        'expired'  => 'danger',
                        default    => 'gray',
                    }),

                // ── Timeline ──────────────────────────────────
                TextColumn::make('starts_at')
                    ->label('Started')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('ends_at')
                    ->label('Expires')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn ($record) => $record?->ends_at?->isPast()
                        ? 'danger'
                        : 'success'
                    ),

                TextColumn::make('trial_ends_at')
                    ->label('Trial Ends')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                // ── Gateway ───────────────────────────────────
                TextColumn::make('gateway')
                    ->label('Gateway')
                    ->badge()
                    ->color(fn (?string $state): string => match (strtolower($state ?? '')) {
                        'stripe'   => 'info',
                        'razorpay' => 'warning',
                        'cashfree' => 'success',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state) => strtoupper($state ?? '-'))
                    ->toggleable(isToggledHiddenByDefault: true),

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
                        'trial'    => 'Trial',
                        'active'   => 'Active',
                        'paused'   => 'Paused',
                        'cancelled'=> 'Cancelled',
                        'expired'  => 'Expired',
                        'past_due' => 'Past Due',
                    ]),

                SelectFilter::make('plan')
                    ->relationship('plan', 'name'),

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