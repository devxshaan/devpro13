<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // ── Order Overview ────────────────────────────
                Section::make('Order Details')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('order_key')
                                    ->label('Order ID')
                                    ->badge()
                                    ->color('gray')
                                    ->copyable(),

                                TextEntry::make('user.name')
                                    ->label('Customer'),

                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'confirmed'  => 'success',
                                        'completed'  => 'success',
                                        'pending'    => 'warning',
                                        'processing' => 'info',
                                        'cancelled'  => 'danger',
                                        'refunded'   => 'danger',
                                        'failed'     => 'danger',
                                        'draft'      => 'gray',
                                        default      => 'gray',
                                    }),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('orderable_type')
                                    ->label('Order Type')
                                    ->formatStateUsing(fn ($state) => class_basename($state))
                                    ->badge()
                                    ->color('info'),

                                TextEntry::make('fulfillment_type')
                                    ->label('Fulfillment')
                                    ->badge()
                                    ->color(fn (?string $state): string => match ($state) {
                                        'digital'  => 'info',
                                        'physical' => 'warning',
                                        'service'  => 'success',
                                        'pickup'   => 'gray',
                                        default    => 'gray',
                                    })
                                    ->placeholder('-'),
                            ]),
                    ]),

                // ── Pricing ───────────────────────────────────
                Section::make('Pricing Breakdown')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('subtotal')
                                    ->label('Subtotal')
                                    ->money(fn ($record) => $record->currency ?? 'USD'),

                                TextEntry::make('discount')
                                    ->label('Discount')
                                    ->money(fn ($record) => $record->currency ?? 'USD'),

                                TextEntry::make('tax')
                                    ->label('Tax')
                                    ->money(fn ($record) => $record->currency ?? 'USD'),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextEntry::make('shipping')
                                    ->label('Shipping')
                                    ->money(fn ($record) => $record->currency ?? 'USD'),

                                TextEntry::make('total')
                                    ->label('Total')
                                    ->money(fn ($record) => $record->currency ?? 'USD')
                                    ->weight('bold')
                                    ->color('success')
                                    ->size('lg'),

                                TextEntry::make('coupon_code')
                                    ->label('Coupon Used')
                                    ->badge()
                                    ->color('warning')
                                    ->placeholder('No coupon'),
                            ]),
                    ]),

                // ── Notes ─────────────────────────────────────
                Section::make('Notes')
                    ->collapsed()
                    ->schema([
                        TextEntry::make('notes')
                            ->label('Customer Notes')
                            ->placeholder('No notes')
                            ->columnSpanFull(),

                        TextEntry::make('admin_notes')
                            ->label('Admin Notes')
                            ->placeholder('No admin notes')
                            ->columnSpanFull(),
                    ]),

                // ── Timeline ──────────────────────────────────
                Section::make('Timeline')
                    ->collapsed()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Ordered At')
                                    ->dateTime('d M Y, h:i A'),

                                TextEntry::make('confirmed_at')
                                    ->label('Confirmed At')
                                    ->dateTime('d M Y, h:i A')
                                    ->placeholder('-'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('completed_at')
                                    ->label('Completed At')
                                    ->dateTime('d M Y, h:i A')
                                    ->placeholder('-'),

                                TextEntry::make('cancelled_at')
                                    ->label('Cancelled At')
                                    ->dateTime('d M Y, h:i A')
                                    ->placeholder('-'),
                            ]),
                    ]),

                // ── Deleted ───────────────────────────────────
                Section::make('Deleted')
                    ->visible(fn (Order $record): bool => $record->trashed())
                    ->schema([
                        TextEntry::make('deleted_at')
                            ->label('Deleted At')
                            ->dateTime('d M Y, h:i A')
                            ->color('danger'),
                    ]),
            ]);
    }
}