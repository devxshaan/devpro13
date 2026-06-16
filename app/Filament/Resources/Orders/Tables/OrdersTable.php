<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                // ── Identity ──────────────────────────────────
                TextColumn::make('order_key')
                    ->label('Order ID')
                    ->badge()
                    ->color('gray')
                    ->copyable()
                    ->searchable(),

                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                // ── Type ──────────────────────────────────────
                TextColumn::make('orderable_type')
                    ->label('Type')
                    ->formatStateUsing(fn ($state) => class_basename($state))
                    ->badge()
                    ->color('info'),

                // ── AMOUNT (FIXED) ────────────────────────────
                TextColumn::make('total')
                    ->label('Total')
                    ->sortable()
                    ->formatStateUsing(function ($state, $record) {
                        return $record->formatted('total');
                    }),

                // ── Status ────────────────────────────────────
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'confirmed', 'completed' => 'success',
                        'pending'               => 'warning',
                        'processing'            => 'info',
                        'cancelled', 'refunded', 'failed' => 'danger',
                        'draft'                 => 'gray',
                        default                 => 'gray',
                    }),

                // ── Fulfillment ───────────────────────────────
                TextColumn::make('fulfillment_type')
                    ->label('Fulfillment')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'digital'  => 'info',
                        'physical' => 'warning',
                        'service'  => 'success',
                        'pickup'   => 'gray',
                        default    => 'gray',
                    }),

                // ── Date ──────────────────────────────────────
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),

                TextColumn::make('confirmed_at')
                    ->label('Confirmed')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft'      => 'Draft',
                        'pending'    => 'Pending',
                        'confirmed'  => 'Confirmed',
                        'processing' => 'Processing',
                        'completed'  => 'Completed',
                        'cancelled'  => 'Cancelled',
                        'refunded'   => 'Refunded',
                        'failed'     => 'Failed',
                    ]),

                SelectFilter::make('fulfillment_type')
                    ->label('Fulfillment')
                    ->options([
                        'digital'  => 'Digital',
                        'physical' => 'Physical',
                        'service'  => 'Service',
                        'pickup'   => 'Pickup',
                    ]),

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