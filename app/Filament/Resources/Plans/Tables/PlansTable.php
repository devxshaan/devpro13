<?php

namespace App\Filament\Resources\Plans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PlansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                // ── Identity ──────────────────────────────────
                TextColumn::make('plan_key')
                    ->label('Plan ID')
                    ->badge()
                    ->color('gray')
                    ->copyable()
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Plan Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                // ── Pricing ───────────────────────────────────
                // ── Pricing ───────────────────────────────────
                TextColumn::make('price')
                    ->label('Price')
                    ->formatStateUsing(fn ($state, $record) =>
                        ($record->currency ?? 'USD') . ' ' . number_format($state, 2)
                    ), 

                TextColumn::make('billing_cycle')
                    ->label('Billing')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'one_time' => 'success',
                        'monthly'  => 'info',
                        'yearly'   => 'warning',
                        'daily'    => 'gray',
                        'weekly'   => 'gray',
                        'per_use'  => 'danger',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'one_time' => 'One Time',
                        'daily'    => 'Daily',
                        'weekly'   => 'Weekly',
                        'monthly'  => 'Monthly',
                        'yearly'   => 'Yearly',
                        'per_use'  => 'Per Use',
                        default    => $state,
                    }),

                TextColumn::make('trial_days')
                    ->label('Trial')
                    ->suffix(' days')
                    ->sortable(),

                // ── Status ────────────────────────────────────
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean(),

                // ── Subscribers ───────────────────────────────
                TextColumn::make('subscriptions_count')
                    ->label('Subscribers')
                    ->counts('subscriptions')
                    ->badge()
                    ->color('success'),

                // ── Date ──────────────────────────────────────
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
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