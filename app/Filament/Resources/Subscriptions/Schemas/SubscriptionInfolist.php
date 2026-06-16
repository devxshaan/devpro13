<?php

namespace App\Filament\Resources\Subscriptions\Schemas;

use App\Models\Subscription;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

use Filament\Infolists\Components\IconEntry;

class SubscriptionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // ── Subscription Overview ─────────────────────
                Section::make('Subscription Details')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('subscription_key')
                                    ->label('Subscription ID')
                                    ->badge()
                                    ->color('gray')
                                    ->copyable(),

                                TextEntry::make('user.name')
                                    ->label('Customer'),

                                TextEntry::make('status')
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
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('plan.name')
                                    ->label('Plan')
                                    ->badge()
                                    ->color('info'),

                                TextEntry::make('price_at_subscription')
                                    ->label('Price at Purchase')
                                    ->money(fn ($record) => $record->currency ?? 'USD')
                                    ->weight('bold')
                                    ->color('success'),
                            ]),
                    ]),

                // ── Timeline ──────────────────────────────────
                Section::make('Subscription Timeline')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('starts_at')
                                    ->label('Started At')
                                    ->dateTime('d M Y')
                                    ->placeholder('-'),

                                TextEntry::make('ends_at')
                                    ->label('Expires At')
                                    ->dateTime('d M Y')
                                    ->color(fn ($record) => $record?->ends_at?->isPast()
                                        ? 'danger'
                                        : 'success'
                                    )
                                    ->placeholder('-'),

                                TextEntry::make('trial_ends_at')
                                    ->label('Trial Ends At')
                                    ->dateTime('d M Y')
                                    ->placeholder('No trial'),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextEntry::make('cancelled_at')
                                    ->label('Cancelled At')
                                    ->dateTime('d M Y, h:i A')
                                    ->color('danger')
                                    ->placeholder('-'),

                                TextEntry::make('paused_at')
                                    ->label('Paused At')
                                    ->dateTime('d M Y, h:i A')
                                    ->color('warning')
                                    ->placeholder('-'),

                                TextEntry::make('resumed_at')
                                    ->label('Resumed At')
                                    ->dateTime('d M Y, h:i A')
                                    ->color('success')
                                    ->placeholder('-'),
                            ]),
                    ]),

                // ── Gateway ───────────────────────────────────
                Section::make('Gateway Information')
                    ->collapsed()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('gateway')
                                    ->label('Gateway')
                                    ->badge()
                                    ->color(fn (?string $state): string => match (strtolower($state ?? '')) {
                                        'stripe'   => 'info',
                                        'razorpay' => 'warning',
                                        'cashfree' => 'success',
                                        default    => 'gray',
                                    })
                                    ->formatStateUsing(fn (?string $state) => strtoupper($state ?? '-')),

                                TextEntry::make('gateway_subscription_id')
                                    ->label('Gateway Subscription ID')
                                    ->copyable()
                                    ->placeholder('-'),
                            ]),
                    ]),

                // ── Meta ──────────────────────────────────────
                Section::make('Timeline')
                    ->collapsed()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Created At')
                                    ->dateTime('d M Y, h:i A'),

                                TextEntry::make('updated_at')
                                    ->label('Last Updated')
                                    ->dateTime('d M Y, h:i A'),
                            ]),
                    ]),

                // ── Deleted ───────────────────────────────────
                Section::make('Deleted')
                    ->visible(fn (Subscription $record): bool => $record->trashed())
                    ->schema([
                        TextEntry::make('deleted_at')
                            ->label('Deleted At')
                            ->dateTime('d M Y, h:i A')
                            ->color('danger'),
                    ]),
            ]);
    }
}