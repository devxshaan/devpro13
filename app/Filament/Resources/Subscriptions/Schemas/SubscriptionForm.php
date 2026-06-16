<?php

namespace App\Filament\Resources\Subscriptions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('subscription_key')
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('plan_id')
                    ->relationship('plan', 'name')
                    ->required(),
                TextInput::make('price_at_subscription')
                    ->required()
                    ->numeric(),
                TextInput::make('currency')
                    ->default(null),
                Select::make('status')
                    ->options([
            'trial' => 'Trial',
            'active' => 'Active',
            'paused' => 'Paused',
            'cancelled' => 'Cancelled',
            'expired' => 'Expired',
            'past_due' => 'Past due',
        ])
                    ->default('trial')
                    ->required(),
                DateTimePicker::make('trial_ends_at'),
                DateTimePicker::make('starts_at'),
                DateTimePicker::make('ends_at'),
                DateTimePicker::make('cancelled_at'),
                DateTimePicker::make('paused_at'),
                DateTimePicker::make('resumed_at'),
                TextInput::make('gateway')
                    ->default(null),
                TextInput::make('gateway_subscription_id')
                    ->default(null),
                Textarea::make('metadata')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
