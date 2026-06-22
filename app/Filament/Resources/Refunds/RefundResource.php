<?php

namespace App\Filament\Resources\Refunds;

use App\Filament\Resources\Refunds\Pages\ManageRefunds;
use App\Models\Refund;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Nexbolt\Core\Services\Payment\RefundService;

class RefundResource extends Resource
{
    protected static ?string $model = Refund::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUturnLeft;

    protected static ?string $recordTitleAttribute = 'refund_key';

    // ✅ Naya refund manually create karna allowed nahi — flow hamesha
    // request() se shuru hona chahiye (user ya admin "request" karega)
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('refund_key')
            ->columns([
                TextColumn::make('refund_key')
                    ->label('Refund ID')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable(),

                TextColumn::make('amount')
                    ->money(fn ($record) => $record->currency)
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'requested', 'pending' => 'warning',
                        'approved', 'processing' => 'info',
                        'completed' => 'success',
                        'rejected', 'failed', 'cancelled' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Requested On')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),

                // ── Toggleable / hidden by default — zarurat pe on karo ──
                TextColumn::make('payment.payment_key')
                    ->label('Payment')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('refund_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'gateway' => 'info',
                        'manual' => 'gray',
                        'store_credit' => 'warning',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('reason')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->reason)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('requestedBy.name')
                    ->label('Requested By')
                    ->default('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('approvedBy.name')
                    ->label('Approved By')
                    ->default('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('processedBy.name')
                    ->label('Processed By')
                    ->default('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'requested' => 'Requested',
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'rejected' => 'Rejected',
                        'failed' => 'Failed',
                        'cancelled' => 'Cancelled',
                    ]),

                SelectFilter::make('refund_type')
                    ->options([
                        'gateway' => 'Gateway',
                        'manual' => 'Manual',
                        'store_credit' => 'Store Credit',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                    Action::make('approve')
                        ->label('Approve')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn ($record) => in_array($record->status, ['requested', 'pending']))
                        ->requiresConfirmation()
                        ->modalHeading('Approve Refund Request')
                        ->modalDescription(fn ($record) => "Customer requested {$record->amount} {$record->currency}. You can adjust the amount if needed before approving.")
                        ->schema([
                            TextInput::make('approved_amount')
                                ->label('Refund Amount')
                                ->numeric()
                                ->required()
                                ->prefix(fn ($record) => $record->currency)
                                ->default(fn ($record) => $record->amount)
                                ->helperText(fn ($record) => "Maximum refundable: {$record->amount} {$record->currency}")
                                ->rules([
                                    fn ($record) => function (string $attribute, $value, \Closure $fail) use ($record) {
                                        if ($value > $record->amount) {
                                            $fail("Approved amount cannot exceed the requested amount ({$record->amount}).");
                                        }
                                        if ($value <= 0) {
                                            $fail('Amount must be greater than zero.');
                                        }
                                    },
                                ]),
                        ])
                        ->action(function (Refund $record, array $data) {
                            
                            if ((float) $data['approved_amount'] !== (float) $record->amount) {
                                $record->update(['amount' => $data['approved_amount']]);
                            }

                            app(RefundService::class)->approve($record, auth()->user());

                            Notification::make()
                                ->title('Refund approved')
                                ->body("Approved amount: {$data['approved_amount']} {$record->currency}")
                                ->success()
                                ->send();
                        }),

                    Action::make('process')
                        ->label('Process')
                        ->icon('heroicon-o-banknotes')
                        ->color('primary')
                        ->visible(fn ($record) => in_array($record->status, ['approved', 'pending']))
                        ->requiresConfirmation()
                        ->modalHeading('Process this refund?')
                        ->modalDescription('This will trigger the actual refund on the gateway (if applicable) and cannot be undone.')
                        ->action(function (Refund $record) {
                            try {
                                app(RefundService::class)->process($record, auth()->user());

                                Notification::make()
                                    ->title('Refund processed successfully')
                                    ->success()
                                    ->send();
                            } catch (\Throwable $e) {
                                Notification::make()
                                    ->title('Refund processing failed')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Action::make('reject')
                        ->label('Reject')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn ($record) => in_array($record->status, ['requested', 'pending', 'approved']))
                        ->requiresConfirmation()
                        ->modalHeading('Reject this refund request?')
                        ->schema([
                            Textarea::make('note')
                                ->label('Rejection reason')
                                ->required(),
                        ])
                        ->action(function (Refund $record, array $data) {
                            app(RefundService::class)->reject($record, auth()->user(), $data['note']);

                            Notification::make()
                                ->title('Refund rejected')
                                ->success()
                                ->send();
                        }),
                ])
            ->toolbarActions([
                // ... same
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageRefunds::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}