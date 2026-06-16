<?php

namespace App\Filament\Resources\Plans\Schemas;

use App\Models\Plan;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PlanInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // ── Plan Overview ─────────────────────────────
                Section::make('Plan Overview')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('plan_key')
                                    ->label('Plan ID')
                                    ->badge()
                                    ->color('gray')
                                    ->copyable(),

                                TextEntry::make('name')
                                    ->label('Plan Name')
                                    ->weight('bold'),

                                TextEntry::make('billing_cycle')
                                    ->label('Billing')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'one_time' => 'success',
                                        'monthly'  => 'info',
                                        'yearly'   => 'warning',
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
                            ]),

                        TextEntry::make('description')
                            ->label('Description')
                            ->placeholder('No description provided')
                            ->columnSpanFull(),
                    ]),

                // ── Pricing ───────────────────────────────────
                Section::make('Pricing')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('price')
                                    ->label('Price')
                                    ->money(fn () => \App\Models\Setting::get('default_currency', 'USD'))
                                    ->size('lg')
                                    ->weight('bold')
                                    ->color('success'),

                                TextEntry::make('trial_days')
                                    ->label('Free Trial Period')
                                    ->suffix(' days')
                                    ->placeholder('No trial'),
                            ]),
                    ]),

                // ── Features ──────────────────────────────────
                Section::make('Features')
                    ->schema([
                        TextEntry::make('features')
                            ->label('Included Features')
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->placeholder('No features listed')
                            ->columnSpanFull(),
                    ]),

                // ── Status ────────────────────────────────────
                Section::make('Status')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                IconEntry::make('is_active')
                                    ->label('Active')
                                    ->boolean(),

                                IconEntry::make('is_featured')
                                    ->label('Featured / Most Popular')
                                    ->boolean(),
                            ]),
                    ]),

                // ── Subscribers ───────────────────────────────
                Section::make('Usage')
                    ->schema([
                        TextEntry::make('subscriptions_count')
                            ->label('Total Subscribers')
                            ->state(fn (Plan $record): int => $record->subscriptions()->count())
                            ->badge()
                            ->color('info'),
                    ]),

                // ── Timestamps ────────────────────────────────
                Section::make('Timeline')
                    ->collapsed()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Created')
                                    ->dateTime('d M Y, h:i A'),

                                TextEntry::make('updated_at')
                                    ->label('Last Updated')
                                    ->dateTime('d M Y, h:i A'),
                            ]),

                        TextEntry::make('deleted_at')
                            ->label('Deleted At')
                            ->dateTime('d M Y, h:i A')
                            ->visible(fn (Plan $record): bool => $record->trashed())
                            ->color('danger'),
                    ]),
            ]);
    }
}