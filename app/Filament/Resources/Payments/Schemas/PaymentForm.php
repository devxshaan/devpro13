<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('payment_key')
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('subscription_id')
                    ->relationship('subscription', 'id')
                    ->default(null),
                TextInput::make('payable_type')
                    ->default(null),
                TextInput::make('payable_id')
                    ->numeric()
                    ->default(null),
                TextInput::make('gateway')
                    ->required(),
                TextInput::make('gateway_payment_id')
                    ->default(null),
                TextInput::make('gateway_order_id')
                    ->default(null),
                Textarea::make('gateway_response')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                TextInput::make('amount_refunded')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('currency')
                    ->default(null),
                TextInput::make('payment_method')
                    ->default(null),
                Select::make('status')
                    ->options([
            'pending' => 'Pending',
            'processing' => 'Processing',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'cancelled' => 'Cancelled',
            'refunded' => 'Refunded',
            'partial_refund' => 'Partial refund',
        ])
                    ->default('pending')
                    ->required(),
                DateTimePicker::make('paid_at'),
                DateTimePicker::make('refunded_at'),
                DateTimePicker::make('failed_at'),
                Textarea::make('notes')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('ip_address')
                    ->default(null),
                Textarea::make('metadata')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
