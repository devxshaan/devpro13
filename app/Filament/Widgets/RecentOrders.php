<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentOrders extends TableWidget
{
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'Recent Orders';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Order::with('user')->latest()->limit(10))
            ->columns([
                TextColumn::make('order_key')
                    ->label('Order ID')
                    ->badge()
                    ->color('gray')
                    ->copyable()
                    ->searchable(),

                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable(),

                TextColumn::make('orderable_type')
                    ->label('Type')
                    ->formatStateUsing(fn ($state) => class_basename($state))
                    ->badge()
                    ->color('info'),

                TextColumn::make('total')
                    ->label('Total')
                    ->formatStateUsing(fn ($state, $record) => $record->formatted_total)
                    ->weight('bold'),
                SelectColumn::make('status')
                ->options([
                'draft'      => '⏸ Draft',
                'pending'    => '⏳ Pending',
                'confirmed'  => '✅ Confirmed',
                'completed'  => '🎉 Completed',
                'cancelled'  => '❌ Cancelled',
                'refunded'   => '↩️ Refunded',
                'failed'     => '🚫 Failed',
            ])
                ->selectablePlaceholder(false),
                TextColumn::make('created_at')
    ->label('Ordered')
    ->description(fn ($record) => $record->created_at->format('d M Y, h:i A'))
    ->since(),
            ]);
    }
}