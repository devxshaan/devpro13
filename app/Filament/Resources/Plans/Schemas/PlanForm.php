<?php

namespace App\Filament\Resources\Plans\Schemas;


use App\Models\Setting;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // ── Basic Info ────────────────────────────────
                Section::make('Plan Details')
                    ->description('Basic information about this plan')
                    ->schema([
                        TextInput::make('name')
                            ->label('Plan Name')
                            ->placeholder('e.g. Gold Plan, Basic, Premium')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('slug', Str::slug($state));
                            }),

                        // ── Hidden — Auto Generate ─────────────
                        TextInput::make('slug')
                            ->hidden(), // Auto se name se generate hoga

                        Textarea::make('description')
                            ->label('Plan Description')
                            ->placeholder('What does this plan include?')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                // ── Pricing ───────────────────────────────────
                Section::make('Pricing')
                    ->description('Set the price and billing details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('price')
                                    ->label('Price')
                                    ->numeric()
                                    ->prefix(fn () => Setting::get('currency_symbol', '$'))
                                    ->default(0)
                                    ->required(),

                                Select::make('billing_cycle')
                                    ->label('Billing Cycle')
                                    ->options([
                                        'one_time' => '💳 One Time Payment',
                                        'daily'    => '📅 Daily',
                                        'weekly'   => '📅 Weekly',
                                        'monthly'  => '📅 Monthly',
                                        'yearly'   => '📅 Yearly',
                                        'per_use'  => '🎯 Per Use',
                                    ])
                                    ->default('monthly')
                                    ->required(),
                            ]),

                        TextInput::make('trial_days')
                            ->label('Free Trial Days')
                            ->helperText('Set 0 for no trial period')
                            ->numeric()
                            ->default(0)
                            ->suffix('days'),
                    ]),

                // ── Features ──────────────────────────────────
                Section::make('Features')
                    ->description('List what this plan includes — shown to customers')
                    ->schema([
                        TagsInput::make('features')
                            ->label('Plan Features')
                            ->placeholder('Type a feature and press Enter')
                            ->helperText('e.g. Unlimited users, 24/7 support, API access')
                            ->columnSpanFull(),
                    ]),

                // ── Visibility ────────────────────────────────
                Section::make('Display Settings')
                    ->description('Control how this plan appears')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->helperText('Inactive plans won\'t be shown to customers')
                                    ->default(true),

                                Toggle::make('is_featured')
                                    ->label('Featured / Most Popular')
                                    ->helperText('Highlight this plan with a badge')
                                    ->default(false),
                            ]),
                    ]),
            ]);
    }
}